<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_order_id',
        'document_type',
        'file_path',
    ];

    /**
     * Relationship with ShipmentOrder.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shipmentOrder()
    {
        return $this->belongsTo(ShipmentOrder::class);
    }
}
