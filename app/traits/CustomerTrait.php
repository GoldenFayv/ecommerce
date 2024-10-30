<?php

namespace App\traits;

use App\Models\Customer;

trait CustomerTrait
{
    public function createCustomer($payload)
    {
        $customer = new Customer();
        $customer->first_name = $payload['first_name'];
        $customer->last_name = $payload['last_name'];
        $customer->mobile_number = $payload['mobile_number'];
        // $customer->created_by = $payload['created_by'] ?? null;
        // $customer->isAdmin = $payload['isAdmin'] ?? false;
        $customer->save();

        return $customer;
    }

    public function getCustomerDetails(Customer $customer = null, $customerId = null){

        $customer ??= Customer::findOrFail($customerId);
        return [
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'mobile_numebr' => $customer->mobile_number
        ];
    }
}
