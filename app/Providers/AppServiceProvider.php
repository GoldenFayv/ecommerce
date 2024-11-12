<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\ShipmentOrder;
use App\Observers\CustomerObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Observers\ShipmentOrderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        ShipmentOrder::observe(ShipmentOrderObserver::class);
        Customer::observe(CustomerObserver::class);
    }
}
