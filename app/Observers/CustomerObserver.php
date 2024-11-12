<?php

namespace App\Observers;

use App\Models\Customer;

class CustomerObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(Customer $customer): void
    {
        //
    }

    /**
     * Handle the customer "creating" event.
     */
    public function creating(Customer $customer): void
    {
        // $customer->shipper_code = customerShipperCode();
    }
    /**
     * Handle the customer "updated" event.

     */
    public function updated(Customer $customer): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(Customer $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(Customer $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(Customer $user): void
    {
        //
    }
}
