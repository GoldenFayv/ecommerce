<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'customer_id',
        'origin_address_id',
        'destination_address_id',
        'drop_off_point_id',
        'cargo_description',
        'mod_of_shipment',
        'types_of_goods',
        'agent_code',
        'status',
        'verified',
        'total_weight',
        'route_code',
        'route_type',
        'shipping_code',
        'estimated_cost',
        'total_declared_value',
        'declaration',
        'origin_zone_id',
        'destination_zone_id',
        'chargeable_weight',
        'volumetric_weight',
    ];

    /**
     * Relationship with ShipmentItems.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shipmentItems()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    /**
     * Relationship with ShipmentDocuments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shipmentDocuments()
    {
        return $this->hasMany(ShipmentDocument::class);
    }

    /**
     * Relationship with the customer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship with origin address.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function originAddress()
    {
        return $this->belongsTo(Address::class, 'origin_address_id');
    }

    /**
     * Relationship with destination address.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function destinationAddress()
    {
        return $this->belongsTo(Address::class, 'destination_address_id');
    }

    /**
     * Relationship with drop-off point.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dropOffPoint()
    {
        return $this->belongsTo(DropOffPoint::class);
    }

    public function originZone(){
        return $this->belongsTo(ShipmentZone::class, 'origin_zone_id');
    }
    public function destinationZone(){
        return $this->belongsTo(ShipmentZone::class, 'destination_zone_id');
    }
}
