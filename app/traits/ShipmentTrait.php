<?php

namespace App\traits;

use Exception;
use Carbon\Carbon;
use App\Models\Address;
use App\Models\Billing;
use App\Models\Courier;
use App\Models\Package;
use App\Models\Discount;
use App\Models\Shipment;
use App\Enums\AddressType;
use App\Exceptions\CustomException;
use App\Models\CustomsDocument;

trait ShipmentTrait
{
    public function getshipmentDetails(Shipment $shipment)
    {
        $courier = Courier::find($shipment->courier_id);

        $estimatedDeliveryDate = $this->calculateDeliveryDate(1, $courier->max_delivery_days, $courier->cutoff_time);

        $package = $this->getPackageDetails($shipment->id);
        $shipmentData = [
            'id' => $shipment->id,
            'status' => $shipment->status,
            'shipment_reference' => $shipment->shipment_reference,
            'shipment_date' => $shipment->shipment_date,
            'mode_of_shipment' => $shipment->mode_of_shipment,
            'priority_level' => $shipment->priority_level,
            'cargo_description' => $shipment->cargo_description,
            'carrier' => $shipment->carrier,
            'courier' => $shipment->courier->name,
            'shipping_method' => $shipment->shipping_method,
            'tracking_service' => $shipment->tracking_service,
            'signature_required' => $shipment->signature_required,
            'user_id' => $shipment->user_id,
            'user' => $shipment->user->name,

            'package' => $package,

            'billing' => $this->getBillingDetails($shipment->id),

            'origin_address' => $this->getAddressDetails($shipment->id, AddressType::ORIGIN()),

            'destination_address' => $this->getAddressDetails($shipment->id, AddressType::DESTINATION()),

            'custom_documents' => $this->getCustomDocuments($shipment->id),

            'estimatedDeliveryDate' => $estimatedDeliveryDate,

            'total_cost' => $this->calculateTotalCost($package)
        ];

        return $shipmentData;
    }

    private function getPackageDetails(int $shipmentId)
    {
        $package = Package::where('shipment_id', $shipmentId)->first();

        if (!$package) {
            throw new CustomException('Package not found');
        }

        return [
            'package_id' => $package->id,
            'number_of_packages' => $package->number_of_packages,
            'weight' => $package->weight,
            'length' => $package->length,
            'width' => $package->width,
            'height' => $package->height,
            'shipment_value' => $package->shipment_value,
            'insurance' => $package->insurance,
            'shipment_contents' => $package->shipment_contents,
            'fragile' => $package->fragile,
            'hazardous_materials' => $package->hazardous_materials,
            'package_description' => $package->package_description
        ];
    }

    private function getBillingDetails(int $shipmentId)
    {
        $billing = Billing::where('shipment_id', $shipmentId)->first();

        if (!$billing) {
            throw new CustomException('Billing not found');
        }

        return [
            'payment_method' => $billing->payment_method,
            'billing_address' => $billing->billing_address,
            'coupon_code' => $billing->coupon_code,
        ];
    }

    private function getAddressDetails(int $shipmentId, $type)
    {
        $address = Address::where(['shipment_id' => $shipmentId, 'type' => $type])->first();

        if (!$address) {
            throw new CustomException('Address not found');
        }

        return [
            'type' => $address->type,
            'name' => $address->name,
            'mobile_number' => $address->mobile_number,
            'email' => $address->email,
            'street_address' => $address->street_address,
            'city' => $address->city,
            'lga' => $address->lga,
            'state' => $address->state,
            'postal_code' => $address->postal_code,
            'country' => $address->country,
            'preferred_datetime' => $address->preferred_datetime,
            'special_instructions' => $address->special_instructions,
        ];
    }

    private function getCustomDocuments(int $shipmentId)
    {
        $customDoument = CustomsDocument::where('shipment_id', $shipmentId)->first();

        if (!$customDoument) {
            return [];
        }

        return [
            'document_type' => $customDoument->document_type,
            'files' => $customDoument->files
        ];
    }

    public function getDeliveryDays($shippingMethod, $priorityLevel)
    {
        $deliveryDays = [
            'air' => ['standard' => 5, 'priority' => 2],
            'sea' => ['standard' => 20, 'priority' => 15],
            'land' => ['standard' => 10, 'priority' => 5]
        ];

        return $deliveryDays[$shippingMethod][$priorityLevel] ?? 0; // Default to 0 if not found
    }

    /**
     * Calculate total cost based on shipment details
     */
    protected function calculateTotalCost($data)
    {
        $baseCost = 100; // Example base cost, can be fetched from database or config
        $weightCharge = $data['weight'] * 2; // Example charge per weight unit
        $insuranceCost = $data['insurance'] ? 50 : 0; // Example insurance cost

        $totalCost = $baseCost + $weightCharge + $insuranceCost;

        if (!empty($data['billing']['coupon'])) {
            $discount_code = Discount::where('code', $data['billing']['coupon'])->first();
            if (!empty($discount_code)) {
                $totalCostAfterDiscount = $discount_code->getDiscountedValue($totalCost);
            }
        }

        // Total cost including base cost, weight charge, insurance, and discount

        return $totalCostAfterDiscount ?? $totalCost;
    }


    public function calculateDeliveryDate(int $processingDays, int $shippingDays, $cutoffTime = null, $holidays = [])
    {
        $now = Carbon::now();

        // Check if current time exceeds cutoff time, if set
        if ($cutoffTime && $now->format('H:i') > $cutoffTime) {
            // Start processing the next business day
            $now->addDay();
        }

        // Add processing days
        $deliveryDate = self::addBusinessDays($now, $processingDays, $holidays);

        // Add shipping days
        $deliveryDate = self::addBusinessDays($deliveryDate, $shippingDays, $holidays);

        return $deliveryDate->format('Y-m-d');
    }

    /**
     * Add business days to a date, skipping weekends and holidays.
     *
     * @param Carbon $date
     * @param int $days
     * @param array $holidays
     * @return Carbon
     */
    private static function addBusinessDays(Carbon $date, int $days, $holidays = [])
    {
        while ($days > 0) {
            $date->addDay();
            if (!$date->isWeekend() && !in_array($date->format('Y-m-d'), $holidays)) {
                $days--;
            }
        }

        return $date;
    }
}
