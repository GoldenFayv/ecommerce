<?php

namespace App\Observers;

use App\Models\ShipmentOrder;

class ShipmentOrderObserver
{
    /**
     * Handle the Shipment "created" event.
     */
    public function created(ShipmentOrder $shipmentOrder): void
    {
        //
    }

    /**
     * Handle the Shipment "creating" event.
     */
    public function creating(ShipmentOrder $shipmentOrder): void
    {
        $shipmentOrder->order_no = generateReference();
    }

    /**
     * Handle the Shipment "updated" event.
     */
    public function updated(ShipmentOrder $shipmentOrder): void
    {
        //
    }

    /**
     * Handle the Shipment "deleted" event.
     */
    public function deleted(ShipmentOrder $shipmentOrder): void
    {
        //
    }

    /**
     * Handle the Shipment "restored" event.
     */
    public function restored(ShipmentOrder $shipmentOrder): void
    {
        //
    }

    /**
     * Handle the Shipment "force deleted" event.
     */
    public function forceDeleted(ShipmentOrder $shipmentOrder): void
    {
        //
    }
}
