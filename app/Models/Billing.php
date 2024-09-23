<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method',
        'billing_address',
        'coupon_code',
        'shipment_id'
    ];

    // Relationships
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id', 'id');
    }
}
