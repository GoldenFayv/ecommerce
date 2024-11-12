<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_order_id',
        'item_name',
        'quantity',
        'weight',
        'length',
        'width',
        'height',
        'remarks',
        'declared_value',
    ];

    public function shipmentOrder(){
        return $this->belongsTo(ShipmentOrder::class, 'shipment_order_id');
    }
}
