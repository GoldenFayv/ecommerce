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
use App\Models\ShipmentOrder;
use App\Models\CustomsDocument;
use App\Exceptions\CustomException;

trait ShipmentTrait
{
    public function getshipmentOrderDetails(ShipmentOrder $shipmentOrder)
    {
        // $courier = Courier::find($shipmentOrder->courier_id);

        // $estimatedDeliveryDate = $this->calculateDeliveryDate(1, $courier->max_delivery_days, $courier->cutoff_time);

        $shipmentData = [
            'order_no' => $shipmentOrder->order_no,
            'origin_address' => $shipmentOrder->originAddress,
            'destination_address' => $shipmentOrder->destinationAddress,
            'drop_off_point' => optional($shipmentOrder->dropOffPoint)->name,
            'cargo_description' => $shipmentOrder->cargo_description,
            'mod_of_shipment' => $shipmentOrder->mod_of_shipment,
            'types_of_goods' => $shipmentOrder->types_of_goods,
            'agent_code' => $shipmentOrder->agent_code,
            'status' => $shipmentOrder->status,
            'verified' => $shipmentOrder->verified,
            'total_weight' => $shipmentOrder->total_weight,
            'route_code' => $shipmentOrder->route_code,
            'route_type' => $shipmentOrder->route_type,
            'shipping_code' => $shipmentOrder->shipping_code,
            'estimated_cost' => $shipmentOrder->estimated_cost,
            'total_declared_value' => $shipmentOrder->total_declared_value,
            'declaration' => $shipmentOrder->declaration,
            'origin_zone' => $shipmentOrder->originZone,
            'destination_zone' => $shipmentOrder->destinationZone,
            'chargeable_weight' => $shipmentOrder->chargeable_weight,
            'volumetric_weight' => $shipmentOrder->volumetric_weight,

            // 'package' => $package,

            // 'billing' => $this->getBillingDetails($shipment->id),

            // 'origin_address' => $this->getAddressDetails($shipment->id, AddressType::ORIGIN()),

            // 'destination_address' => $this->getAddressDetails($shipment->id, AddressType::DESTINATION()),

            // 'custom_documents' => $this->getCustomDocuments($shipment->id) ?? null,

            // 'estimatedDeliveryDate' => $estimatedDeliveryDate,

            // 'total_cost' => $this->calculateTotalCost($package)
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
