<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Enums\AddressType;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class ShipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Allows all authenticated users to proceed with the request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = Auth::user(); // Retrieves the currently authenticated user

        $rules = [
            // Shipment details
            'drop_off_point_id' => 'nullable|exists:drop_off_points,id', // Optional field; must be a valid drop-off point ID if provided
            'cargo_description' => 'nullable|string', // Optional string field describing the cargo
            'mod_of_shipment' => 'required|string|max:255', // Mode of shipment, required
            'types_of_goods' => 'nullable|string|max:255', // Optional field for goods type, max 255 characters
            'agent_code' => 'nullable|string|max:100', // Optional agent code, max 100 characters
            'route_code' => 'nullable|string|max:50', // Optional route code, max 50 characters
            'route_type' => 'required|in:local,international', // Defines the route as either 'local' or 'international'
            'declaration' => 'required|string|max:255', // Required field for declaration, max 255 characters
            'origin_zone_id' => 'required|exists:shipment_zones,id', // Required origin zone ID, must exist in shipment zones table
            'destination_zone_id' => 'required|exists:shipment_zones,id', // Required destination zone ID, must exist in shipment zones table

            // Shipment items array validation
            'items' => 'required|array', // Requires at least one item in the shipment
            'items.*.item_name' => 'required|string|max:255', // Each item requires a name with a max length of 255
            'items.*.quantity' => 'required|integer|min:1', // Quantity must be an integer and at least 1
            'items.*.weight' => 'required|numeric|min:0', // Weight must be a non-negative number
            'items.*.length' => 'nullable|numeric|min:0', // Optional length, non-negative if provided
            'items.*.width' => 'nullable|numeric|min:0', // Optional width, non-negative if provided
            'items.*.height' => 'nullable|numeric|min:0', // Optional height, non-negative if provided
            'items.*.remarks' => 'nullable|string|max:255', // Optional remarks, max 255 characters
            'items.*.declared_value' => 'nullable|numeric|min:0', // Optional declared value, non-negative if provided

            // Documents array validation
            'documents' => 'sometimes|array', // Documents array, optional
            'documents.*.document_type' => 'required|string|max:255', // Document type is required for each document
            'documents.*.file' => 'required|string|max:255', // File path is required for each document

            // Addresses
            'origin_address_id' => [
                'exists:addresses,id',
                function ($attribute, $value, $fail) {
                    if ($value && request()->input('addresses')) {
                        foreach (request()->input('addresses') as $address) {
                            if (isset($address['type']) && $address['type'] === AddressType::ORIGIN) {
                                $fail('The origin_address_id and an address of type origin cannot both be provided.');
                            }
                        }
                    }
                },
            ], // Required if 'addresses' array is not provided
            'destination_address_id' => [
                'required_without:addresses',
                'exists:addresses,id',
                function ($attribute, $value, $fail) {
                    if ($value && request()->input('addresses')) {
                        foreach (request()->input('addresses') as $address) {
                            if (isset($address['type']) && $address['type'] === AddressType::DESTINATION) {
                                $fail('The destination_address_id and an address of type destination cannot both be provided.');
                            }
                        }
                    }
                },
            ], // Required if 'addresses' array is not provided
            'addresses' => 'sometimes|required_without:origin_address_id,destination_address_id|array', // Requires addresses array if specific address IDs are absent
            'addresses.*.type' => ['required', Rule::in(AddressType::getValues())], // Each address must specify type (origin or destination)
            'addresses.*.name' => ['nullable', 'required_if:addresses.*.type,' . AddressType::DESTINATION], // Name is required if address is destination
            'addresses.*.email' => ['nullable', 'required_if:addresses.*.type,' . AddressType::DESTINATION, 'email'], // Email is required for destination addresses
            'addresses.*.phone' => ['required', 'regex:/^\d+$/', 'required_if:addresses.*.type,' . AddressType::DESTINATION], // Phone is required, digits only, for destination
            'addresses.*.country' => 'required', // Country is required for all addresses
            'addresses.*.state' => 'required', // State is required for all addresses
            'addresses.*.lga' => 'sometimes', // Local Government Area, optional
            'addresses.*.city' => 'required', // City is required for all addresses
            'addresses.*.street' => 'required', // Street is required for all addresses
            'addresses.*.latitude' => 'sometimes|numeric', // Latitude is optional, must be numeric if provided
            'addresses.*.longitude' => 'sometimes|numeric', // Longitude is optional, must be numeric if provided
            'addresses.*.postal_code' => 'nullable', // Postal code, optional
        ];

        // Conditional validation for customer_id if the user is not a customer
        if ($user->profile_type !== Customer::class) {
            $rules['customer_id'] = 'required|exists:customers,id'; // customer_id is required if user is not a customer
        }

        return $rules;
    }
}
