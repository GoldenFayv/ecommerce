<?php

namespace App\Observers;

use App\Models\User\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        if (!$user->isAdmin) {
            $user->shipper_code = userShipperCode();
        }
    }
    /**
     * Handle the User "updated" event.

     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
