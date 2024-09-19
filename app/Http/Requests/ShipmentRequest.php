<?php

namespace App\Http\Requests;

use Carbon\Carbon;
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
        return false;
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
            'package_description' => 'required',
            'sender_name' => 'required',
            'sender_email' => 'required|email',
            'sender_number' => ['required', 'regex:/^\d+$/'],
            'pickup_address' => 'required',
            'pickup_date_time' => 'date|nullable',
            'pickup_instruction' => 'nullable',
            'number_of_packages' => 'required|numberic',
            'Weight' => 'required|numeric',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'shipment_value' => ['digits_between:1,5', 'required'],
            'insurance' => 'nullable|boolean',
            'shipment_content' => 'nullable',
            'fragile' => 'boolean|required',
            'hazardous' => 'required|boolean',
            'shipping_method' => ['required', Rule::in(ShippingMethod::getValues())],
            'payment_method' => ['required', Rule::in(PaymentMethod::getValues())],
            'billing_address' => []
        ];
    }
}
