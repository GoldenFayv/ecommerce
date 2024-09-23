<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'name',
        'mobile_number',
        'email',
        'street_address',
        'city',
        'lga',
        'state',
        'postal_code',
        'country',
        'preferred_datetime',
        'special_instructions',
        'shipment_reference',
        'shipment_id'
    ];

    // You can define relationships to `Shipment` here if needed
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }
}
