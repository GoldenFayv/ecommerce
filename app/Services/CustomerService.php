<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{

    public function createCustomer($payload)
    {
        $customer = new Customer();
        $customer->first_name = $payload['first_name'];
        $customer->last_name = $payload['last_name'];
        $customer->mobile_number = $payload['mobile_number'];
        $customer->created_by = $payload['created_by'] ?? null;
        $customer->isAdmin = $payload['isAdmin'] ?? false;
        $customer->save();

        return $customer;
    }
}
