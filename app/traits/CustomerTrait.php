<?php

namespace App\traits;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

trait CustomerTrait
{
    /**
     * Creates a new customer record in the database.
     *
     * @param array $payload An associative array containing customer details.
     *                       - 'first_name': string, the first name of the customer.
     *                       - 'last_name': string, the last name of the customer.
     *                       - 'mobile_number': string, the mobile number of the customer.
     *                       - 'shipper_code': string, the shipper code associated with the customer.
     * @return Customer The newly created Customer object.
     */
    public function createCustomer($payload)
    {
        $customer = new Customer();
        $customer->first_name = $payload['first_name'];
        $customer->last_name = $payload['last_name'];
        $customer->mobile_number = $payload['mobile_number'];
        $customer->shipper_code = $payload['shipper_code'];
        // $customer->created_by = $payload['created_by'] ?? null;
        // $customer->isAdmin = $payload['isAdmin'] ?? false;
        $customer->save();

        return $customer;
    }

    /**
     * Retrieves the details of a customer.
     *
     * @param Customer|null $customer An optional Customer object. If provided, it will be used to get the details.
     * @param int|null $customerId An optional customer ID. If $customer is not provided, this ID will be used to find the customer.
     * @return array An associative array containing the customer's first name, last name, and mobile number.
     */
    public function getCustomerDetails(Customer $customer = null, $customerId = null){

        $customer ??= Customer::findOrFail($customerId);
        return [
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'mobile_numebr' => $customer->mobile_number
        ];
    }

    public function updateCustomer($payload){
        $user = Customer::find($payload['user']['profile_type_id']);

        if (!empty($payload['first_name']))
            $user->first_name = $payload['first_name'];

        if (!empty($payload['last_name']))
            $user->last_name = $payload['last_name'];

        if (!empty($payload['mobile_number']))
            $user->mobile_number = $payload['mobile_number'];

        if (!empty($payload['profile_picture'])) {
            $filename = $this->uploadFile($payload['profile_picture'], 'profile_pictures');
            $user->profile_picture = $filename;
        }
        $user->save();

        return "User Profile Updated";
    }

    public function getCustomerAddresses(Customer $customer = null, int $customerId = null){
        $customer ??= Customer::findOrFail($customerId);
        return $customer->addresses;
    }

    
}
