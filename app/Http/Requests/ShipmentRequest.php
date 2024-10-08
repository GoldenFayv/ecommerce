<?php

namespace App\Http\Requests;

use App\Rules\NotAdmin;
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
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();

        return [
            // 'shipment_date' => ['required', 'after:' . Carbon::now()->addWeek(), 'date'],
            'shipment_mode' => ['required', Rule::in(ShippingMode::getValues())],
            'priority_level' => ['required', Rule::in(PriorityLevel::getValues())],
            'courier_id' => ['required', 'exists:couriers,id'],
            'user_name' => $user->isAdmin ? ['required_with:email,mobile_number'] : ['nullable'],
            'email' => $user->isAdmin ? ['required_with:user_name', 'email'] : ['nullable', 'email'],
            'mobile_number' => $user->isAdmin ? ['required_with:user_name', 'regex:/^\d{11,15}$/'] : ['nullable', 'regex:/^\d{11,15}$/'],
            'user_id' => $user->isAdmin
                ? ['required_without:user_name', new NotAdmin]  // Required for admins, validated with custom rule
                : ['nullable'],



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

            'addresses' => 'required|array',
            'addresses.*.type' => ['required', Rule::in(AddressType::getValues())], // differentiates origin and destination
            'addresses.*.name' => 'required',
            'addresses.*.email' => 'required|email',
            'addresses.*.mobile_number' => ['required', 'regex:/^\d+$/'],
            'addresses.*.preferred_datetime' => ['nullable', 'date'],
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
