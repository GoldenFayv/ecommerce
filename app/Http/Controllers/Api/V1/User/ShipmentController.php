<?php

namespace App\Http\Controllers\Api\V1\User;

use Throwable;
use App\Models\Shipment;
use Illuminate\Http\Request;
use App\Enums\ShipmentStatus;
use App\traits\ShipmentTrait;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ShipmentRequest;

class ShipmentController extends Controller
{
    use ShipmentTrait;

    public $user;

    public function __construct()
    {
        if (Auth::check()) {
            $this->user = Auth::user();
        }
    }
    public function create_shipment(ShipmentRequest $shipmentRequest)
    {
        $validatedData = $shipmentRequest->validated(); // Ensure validation is performed
        try {
            DB::transaction(function () use ($validatedData) {

                // Create shipment
                $shipment = Shipment::create([
                    'shipment_date' => $validatedData['shipment_date'],
                    'shipment_mode' => $validatedData['shipment_mode'],
                    'priority_level' => $validatedData['priority_level'],
                    'user_id' => Auth::user()->id
                ]);

                // Create package associated with the shipment
                $shipment->package()->create([
                    'package_description' => $validatedData['package_description'],
                    'number_of_packages' => $validatedData['number_of_packages'],
                    'weight' => $validatedData['weight'], // Fixed typo: 'Weight' should be 'weight'
                    'length' => $validatedData['length'],
                    'width' => $validatedData['width'],
                    'height' => $validatedData['height'],
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
                        'lga' => $address['lga'],
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

                return $this->successResponse("Shipment Successfully Created");
            }, 2);
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

        if($shipment->status == ShipmentStatus::CANCELLED){
            die();
        }

        $shipment->status = ShipmentStatus::CANCELLED();
        $shipment->save();

        return $this->successResponse('Cancelled');
    }
}
