<?php

namespace App\traits;

use App\Models\Admin;
use App\Models\Customer;

trait AdminTrait
{
    public function createAdmin($payload)
    {
        $customer = new Admin();
        $customer->save();

        return $customer;
    }

    public function updateAdmin(){
        
    }
}
