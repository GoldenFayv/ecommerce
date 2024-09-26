<?php

namespace App\traits;

use Exception;
use App\Models\Address;
use App\Models\Billing;
use App\Models\Package;
use App\Models\Shipment;
use App\Enums\AddressType;
use App\Exceptions\CustomException;

trait ShipmentTrait
{
    public function getshipmentDetails(Shipment $shipment)
    {
        $shipmentData = [
            'id' => $shipment->id,
            'approved' => $shipment->status,
            'shipment_reference' => $shipment->shipment_reference,
            'shipment_date' => $shipment->shipment_date,
            'mode_of_shipment' => $shipment->mode_of_shipment,
            'priority_level' => $shipment->priority_level,
            'cargo_description' => $shipment->cargo_description,
            'carrier' => $shipment->carrier,
            'shipping_method' => $shipment->shipping_method,
            'tracking_service' => $shipment->tracking_service,
            'signature_required' => $shipment->signature_required,
            'user_id' => $shipment->user_id,
            'user' => $shipment->user->name,

            'package' => $this->getPackageDetails($shipment->id),

            'billing' => $this->getBillingDetails($shipment->id),

            'origin_address' => $this->getAddressDetails($shipment->id, AddressType::ORIGIN()),

            'destination_address' => $this->getAddressDetails($shipment->id, AddressType::DESTINATION())
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
}
