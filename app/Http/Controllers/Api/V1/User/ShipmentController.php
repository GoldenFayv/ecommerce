<?php

namespace App\Http\Controllers\Api\V1\User;

use Throwable;
use App\Models\Config;
use App\Models\Courier;
use App\Models\Shipment;
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

            // Prepare shipment summary for user confirmation
            $courier = Courier::find($validatedData['courier_id']);

            $shipmentSummary = [
                'estimatedDeliveryDate' => $this->calculateDeliveryDate(1, $courier->max_delivery_days, $courier->cutoff_time),
                'total_cost' => $this->calculateTotalCost($validatedData)
            ];
            return $this->successResponse("Review Shipment Details");
        } catch (Throwable $th) {
            Log::debug("Caught error", [$th]);
            return $this->failureResponse("Internal Server Error", $th->getMessage());
        }
    }

    public function confirmShipment(Request $request)
    {
        $cacheKey = 'shipment_' . Auth::user()->id;
        $validatedData = Cache::get($cacheKey);

        if (!$validatedData) {
            return $this->failureResponse("No shipment data found. Please start the shipment process again.");
        }

        try {
            DB::transaction(function () use ($validatedData, $cacheKey) {

                // Create shipment
                $shipment = Shipment::create([
                    'shipment_mode' => $validatedData['shipment_mode'],
                    'priority_level' => $validatedData['priority_level'],
                    'user_id' => Auth::user()->id,
                    'courier_id' => $validatedData['courier_id']
                ]);

                // Create package associated with the shipment
                $shipment->package()->create([
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
                ]);

                // Create addresses for the shipment
                foreach ($validatedData['addresses'] as $address) {
                    $shipment->address()->create([
                        'type' => $address['type'], // 'origin' or 'destination'
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
                    ]);
                }

                // Create billing details associated with the shipment
                $shipment->billing()->create([
                    'payment_method' => $validatedData['billing']['method'],
                    'billing_address' => $validatedData['billing']['billing_address'],
                    'coupon_code' => $validatedData['billing']['coupon'],
                ]);

                if (isset($validatedData['international']) && $validatedData['international']) {
                    // Store uploaded files and get file names
                    $fileNames = collect($validatedData['files'])->map(function ($file) {
                        return $this->uploadFile($file, FileUploadPaths::CUSTOM_DOCUMENT);
                    });

                    // Create the custom document with the file names
                    $shipment->customDocument()->create([
                        'document_type' => $validatedData['document_type'],
                        'file_name' => $fileNames->toArray(), // Convert to array for database storage
                    ]);
                }


                // Remove the cached data after saving
                Cache::forget($cacheKey);

                $shipmentDetails = $this->getshipmentDetails($shipment);
                return $this->successResponse("Shipment Successfully Created", $shipmentDetails);
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
        $shipments = Shipment::when(!$this->user->isAdmin, function ($query) {
            // If the user is not an admin, filter by user_id
            return $query->where('user_id', $this->user->id);
        })
            ->when($this->user->isAdmin && $request->status, function ($query) use ($request) {
                // If the user is an admin and status is provided, filter by status
                return $query->where('status', $request->status);
            })->get();

        // Map through the shipments and get details
        return $this->successResponse('Shipments', $shipments->map(fn($shipment) => $this->getShipmentDetails($shipment)));
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
}
