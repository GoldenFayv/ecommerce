<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\ShipmentOrder;
use Throwable;
use App\Models\Admin;
use App\Models\Config;
use App\Models\Address;
use App\Models\Courier;
use App\Models\Customer;
use App\Models\Shipment;
use App\Enums\AddressType;
use App\Models\ShipmentZone;
use Illuminate\Http\Request;
use App\Enums\ShipmentStatus;
use App\traits\ShipmentTrait;
use App\Enums\FileUploadPaths;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\ShipmentRequest;
use App\Models\ShipmentItem;

class ShipmentController extends Controller
{
    use ShipmentTrait;

    public $user;
    public $config;


    public function __construct()
    {
        if (Auth::check()) {
            $this->user = Auth::user();
        }

        // $this->config = Config::get();
    }

    public function create_shipment(ShipmentRequest $shipmentRequest)
    {
        $validatedData = $shipmentRequest->validated(); // Ensure validation is performed

        try {
            // Store the validated data in the cache
            $cacheKey = 'shipment_' . Auth::user()->id;  // Generate a unique cache key for each user
            Cache::put($cacheKey, $validatedData, now()->addMinutes(10)); // Store in cache for 30 minutes

            // $shipmentSummary = [
            //     'estimatedDeliveryDate' => $this->calculateDeliveryDate(1, $courier->max_delivery_days, $courier->cutoff_time),
            //     'total_cost' => $this->calculateTotalCost($validatedData)
            // ];
            return $this->successResponse("Review Shipment Details");
        } catch (Throwable $th) {
            Log::debug("Caught error", [$th]);
            return $this->failureResponse("Internal Server Error", $th->getMessage());
        }
    }

    public function confirmShipment()
    {
        $user = Auth::user();
        $cacheKey = "shipment_{$user->id}";
        $validatedData = Cache::get($cacheKey);

        if (!$validatedData) {
            return $this->failureResponse("No shipment data found. Please start the shipment process again.");
        }

        try {
            DB::transaction(function () use ($validatedData, $cacheKey, $user) {

                if ($validatedData['addresses']) {
                    $addressIds = []; // Initialize an empty array to store the IDs

                    foreach ($validatedData['addresses'] as $address) {
                        $createdAddress = Address::create([
                            'type' => $address['type'], // 'origin' or 'destination'
                            'name' => $address['name'] ?? null,
                            'email' => $address['email'] ?? null,
                            'mobile_number' => $address['mobile_number'] ?? null,
                            'country' => $address['country'],
                            'state' => $address['state'],
                            'lga' => $address['lga'] ?? null,
                            'city' => $address['city'],
                            'street' => $address['street'],
                            'postal_code' => $address['postal_code'],
                            'latitude' => $address['latitude'] ?? null,
                            'longitude' => $address['longitude'] ?? null,
                            'user_id' => $user->id
                        ]);

                        // Store the ID based on type (e.g., 'origin' or 'destination')
                        $addressIds[$address['type']] = $createdAddress->id;
                    }
                    // Access the IDs by type
                    $originAddressId = $addressIds[AddressType::ORIGIN] ?? null;
                    $destinationAddressId = $addressIds[AddressType::DESTINATION] ?? null;
                }

                $shipmentOrder = ShipmentOrder::create([
                    'customer_id' => $user->profile_id,
                    'origin_address_id' => $originAddressId ?? $validatedData['origin_address_id'],
                    'destination_address_id' => $destinationAddressId ?? $validatedData['destination_address_id'],
                    'drop_off_point_id' => $validatedData['drop_off_point_id'] ?? null,
                    'cargo_description' => $validatedData['cargo_description'],
                    'mod_of_shipment' => $validatedData['mod_of_shipment'],
                    'types_of_goods' => $validatedData['types_of_goods'],
                    'agent_code' => $validatedData['agent_code'],
                    'route_code' => $validatedData['route_code'],
                    'route_type' => $validatedData['route_type'],
                    'declaration' => $validatedData['declaration'],
                    'origin_zone_id' => $validatedData['origin_zone_id'],
                    'destination_zone_id' => $validatedData['destination_zone_id'],
                    'shipping_code' => $user->shipping_code
                    // 'chargeable_weight' => $validatedData[''],
                    // 'volumetric_weight' => $validatedData[''],
                ]);

                $totalDeclaredValue = 0;
                $total_weight = 0;
                foreach ($validatedData['items'] as $item) {
                    $shipmentOrder->shipmentItems()->create($item);
                    $totalDeclaredValue += $item['declared_value'];
                    $total_weight += $item['weight'];
                }

                $shipmentOrder->update([
                    'total_weight' => $total_weight,
                    'total_declared_value' => $totalDeclaredValue,
                ]);

                if($validatedData['documents']){
                    foreach($validatedData['documents'] as $document){
                        $shipmentOrder->shipmentDocuments()->create([
                            'document_type' => $document['document_type'],
                            'file_path' => $document['file_path'],
                        ]);
                    }
                }

                // Remove the cached data after saving
                Cache::forget($cacheKey);

                // $shipmentDetails = $this->getshipmentDetails($shipment);
                return $this->successResponse("Shipment Successfully Created");
            }, 1);
        } catch (Throwable $th) {
            Log::debug("Caught error", [$th]);
            return $this->failureResponse("Internal Server Error", $th->getMessage());
        }
    }

    public function shipments(Request $request)
    {
        $request->validate([
            'status' => ['nullable', Rule::in(ShipmentStatus::getValues())]
        ]);
        logger("", [$request->status]);

        // Get the shipments based on the user's role and request status
        $shipments = Shipment::when($this->user->profile == Customer::class, function ($query) {
            // If the user is not an admin, filter by user_id
            return $query->where('user_id', $this->user->id);
        })
            ->when($this->user->isAdmin && $request->status, function ($query) use ($request) {
                // If the user is an admin and status is provided, filter by status
                return $query->where('status', $request->status);
            })->get();

        // Map through the shipments and get details
        return $this->successResponse('Shipments', $shipments->map(fn($shipment) => $this->getshipmentOrderDetails($shipment)));
    }

    public function cancelShipment($shipmentId)
    {
        $shipment = Shipment::where(['id' => $shipmentId, 'user_id' => $this->user->id])->firstOrFail();

        if ($shipment->status == ShipmentStatus::CANCELLED) {
            die();
        }

        $shipment->status = ShipmentStatus::CANCELLED();
        $shipment->save();

        return $this->successResponse('Cancelled');
    }


    public function update_shipment(ShipmentRequest $shipmentRequest, $shipmentId)
    {
        $validatedData = $shipmentRequest->validated(); // Ensure validation is performed

        try {
            // Check if the shipment exists
            $shipment = Shipment::findOrFail($shipmentId);

            // Store the validated data in the cache
            $cacheKey = 'update_shipment_' . Auth::user()->id . '_' . $shipmentId;
            $validatedData['shipmentId'] = $shipment->id;
            Cache::put($cacheKey, $validatedData, now()->addMinutes(10)); // Store in cache for 30 minutes

            // Prepare shipment summary for user confirmation
            $courier = Courier::find($validatedData['courier_id']);

            $shipmentSummary = [
                'estimatedDeliveryDate' => $this->calculateDeliveryDate(1, $courier->max_delivery_days, $courier->cutoff_time),
                'total_cost' => $this->calculateTotalCost($validatedData)
            ];
            return $this->successResponse("Review Shipment Details", $shipmentSummary);
        } catch (Throwable $th) {
            Log::debug("Caught error", [$th]);
            return $this->failureResponse("Internal Server Error", $th->getMessage());
        }
    }

    public function confirmUpdateShipment(Request $request, $shipmentId)
    {
        $cacheKey = 'update_shipment_' . Auth::user()->id . '_' . $shipmentId;
        $validatedData = Cache::get($cacheKey);

        if (!$validatedData) {
            return $this->failureResponse("No update data found. Please start the update process again.");
        }

        try {
            DB::transaction(function () use ($validatedData, $cacheKey) {
                // Retrieve the existing shipment
                $shipment = Shipment::findOrFail($validatedData['shipmentId']);

                // Update shipment fields
                $shipment->update([
                    'shipment_date' => $validatedData['shipment_date'] ?? null,
                    'shipment_mode' => $validatedData['shipment_mode'],
                    'priority_level' => $validatedData['priority_level'],
                    'courier_id' => $validatedData['courier_id'],
                    'user_id' => $this->user->isAdmin ? ($validatedData['user_id'] ?? null) : Auth::user()->id,
                    'user_name' => $validatedData['user_name'] ?? null,
                    'email' => $validatedData['email'] ?? null,
                    'mobile_number' => $validatedData['mobile_number'] ?? null,
                ]);

                // Update or create package associated with the shipment
                $shipment->package()->updateOrCreate(
                    ['shipment_id' => $shipment->id],
                    [
                        'package_description' => $validatedData['package_description'],
                        'number_of_packages' => $validatedData['number_of_packages'],
                        'weight' => $validatedData['weight'],
                        'length' => $validatedData['length'] ?? null,
                        'width' => $validatedData['width'] ?? null,
                        'height' => $validatedData['height'] ?? null,
                        'shipment_value' => $validatedData['shipment_value'],
                        'insurance' => $validatedData['insurance'],
                        'shipment_contents' => $validatedData['shipment_content'],
                        'fragile' => $validatedData['fragile'],
                        'hazardous' => $validatedData['hazardous'],
                        'shipping_method' => $validatedData['shipping_method']
                    ]
                );

                // Sync or update addresses for the shipment
                foreach ($validatedData['addresses'] as $address) {
                    $shipment->address()->updateOrCreate(
                        [
                            'shipment_id' => $shipment->id,
                            'type' => $address['type'],
                        ],
                        [
                            'name' => $address['name'],
                            'email' => $address['email'],
                            'mobile_number' => $address['mobile_number'],
                            'preferred_datetime' => $address['preferred_datetime'],
                            'special_instructions' => $address['special_instructions'],
                            'country' => $address['country'],
                            'state' => $address['state'],
                            'lga' => $address['lga'] ?? null,
                            'city' => $address['city'],
                            'street_address' => $address['street_address'],
                            'postal_code' => $address['postal_code'],
                        ]
                    );
                }

                // Update or create billing details
                $shipment->billing()->updateOrCreate(
                    ['shipment_id' => $shipment->id],
                    [
                        'payment_method' => $validatedData['billing']['method'],
                        'billing_address' => $validatedData['billing']['billing_address'],
                        'coupon_code' => $validatedData['billing']['coupon'] ?? null,
                    ]
                );

                // Handle international shipment documents
                if (isset($validatedData['international']) && $validatedData['international']) {
                    $fileNames = collect($validatedData['files'])->map(function ($file) {
                        return $this->uploadFile($file, FileUploadPaths::CUSTOM_DOCUMENT);
                    });

                    $shipment->customDocument()->updateOrCreate(
                        ['shipment_id' => $shipment->id],
                        [
                            'document_type' => $validatedData['document_type'],
                            'file_name' => $fileNames->toArray(),
                        ]
                    );
                }

                // Clear the cache after a successful update
                Cache::forget($cacheKey);

                $shipmentDetails = $this->getshipmentDetails($shipment);
                return $this->successResponse("Shipment Successfully Updated", $shipmentDetails);
            });
        } catch (Throwable $th) {
            Log::debug("Caught error", [$th]);
            return $this->failureResponse("Internal Server Error", $th->getMessage());
        }
    }

    public function getShipmentZone()
    {
        $user = Auth::user();
        logger($user->profile_type);
        $shipmentZones = ShipmentZone::when($user->profile_type == Customer::class, function ($query) {
            $query->where('is_active', true);
        })->get();

        return $this->successResponse("Shipment Zone", $shipmentZones);
    }

    public function createShipmentZone(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'country_code' => 'required',
            'description' => 'nullable',
        ]);

        ShipmentZone::create($request->all());

        return $this->successResponse('Zone Created');
    }
}
