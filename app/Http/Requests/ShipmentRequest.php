<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use App\Enums\Couriers;
use App\Enums\AddressType;
use App\Enums\DocumentType;
use App\Enums\ShippingMode;
use App\Enums\PaymentMethod;
use App\Enums\PriorityLevel;
use App\Enums\ShippingMethod;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shipment_date' => ['required', 'after:' . Carbon::now()->addWeek(), 'date'],
            'shipment_mode' => ['required', Rule::in(ShippingMode::getValues())],
            'priority_level' => ['required', Rule::in(PriorityLevel::getValues())],
            'courier_id' => ['required', 'exists:couriers,id'],


            'package_description' => 'required',
            'number_of_packages' => 'required|numeric',
            'weight' => 'required|numeric',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'shipment_value' => ['required'],
            'insurance' => 'nullable|boolean',
            'shipment_content' => 'nullable',
            'fragile' => 'boolean|required',
            'hazardous' => 'required|boolean',

            // 'origin_address' => 'required',
            // 'origin_address.sender_name' => 'required',
            // 'origin_address.sender_email' => 'required|email',
            // 'origin_address.sender_number' => ['required', 'regex:/^\d+$/'],
            // 'origin_address.pickup_date_time' => 'date|nullable',
            // 'origin_address.pickup_instruction' => 'nullable',
            // 'origin_address.country' => 'required',
            // 'origin_address.state' => 'required',
            // 'origin_address.lga' => 'required',
            // 'origin_address.city' => 'nullable',
            // 'origin_address.pickup_address' => 'required',
            // 'origin_address.postal_code' => 'nullable',

            // 'destination_address' => 'required',
            // 'destination_address.sender_name' => 'required',
            // 'destination_address.sender_email' => 'required|email',
            // 'destination_address.sender_number' => ['required', 'regex:/^\d+$/'],
            // 'destination_address.pickup_date_time' => 'date|nullable',
            // 'destination_address.pickup_instruction' => 'nullable',
            // 'destination_address.country' => 'required',
            // 'destination_address.state' => 'required',
            // 'destination_address.lga' => 'required',
            // 'destination_address.city' => 'nullable',
            // 'destination_address.pickup_address' => 'required',
            // 'destination_address.postal_code' => 'nullable',

            'addresses' => 'required|array',
            'addresses.*.type' => ['required', Rule::in(AddressType::getValues())], // differentiates origin and destination
            'addresses.*.name' => 'required',
            'addresses.*.email' => 'required|email',
            'addresses.*.mobile_number' => ['required', 'regex:/^\d+$/'],
            'addresses.*.preferred_datetime' => ['nullable'],
            'addresses.*.special_instructions' => 'nullable',
            'addresses.*.country' => 'required',
            'addresses.*.state' => 'required',
            'addresses.*.lga' => 'nullable',
            'addresses.*.city' => 'required',
            'addresses.*.street_address' => 'required',
            'addresses.*.postal_code' => 'nullable',

            'shipping_method' => ['required', Rule::in(ShippingMethod::getValues())],

            'billing' => ['required'],
            'billing.method' => ['required', Rule::in(PaymentMethod::getValues())],
            // 'billing.country' => ['required'],
            // 'billing.state' => ['required'],
            // 'billing.lga' => ['required'],
            // 'billing.street_address' => ['required'],
            'billing.billing_address' => "required",
            'billing.coupon' => ['nullable'],

            'international' => ['boolean', 'nullable'],
            'document_type' => ['required_if:international,true', Rule::in(DocumentType::getValues())],
            'files' => ['required_if:international,true', 'array'],
            'files.*' => ['required', 'file']


        ];
    }
}
