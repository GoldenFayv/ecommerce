<?php

namespace App\traits;

use App\Models\Admin;
use App\Models\Customer;

trait AdminTrait
{
    public function createAdmin($payload)
    {
        $admin = new Admin();
        $admin->first_name = $payload['first_name'];
        $admin->last_name = $payload['last_name'];
        $admin->save();

        return $admin;
    }

    public function updateAdmin(){

    }
}
