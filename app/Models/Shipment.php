<?php

namespace App\Models;

use App\Models\User\User;
use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_reference',
        'shipment_date',
        'mode_of_shipment',
        'priority_level',
        'cargo_description',
        'carrier',
        'shipping_method',
        'tracking_service',
        'signature_required',
        'user_id',
        'status',
        'courier_id',
        'email',
        'user_name',
        'mobile_number'
    ];

    protected $casts = ['tracking_service' => 'boolean', 'signature_required' => 'boolean'];

    public function address()
    {
        return $this->hasMany(Address::class, 'shipment_id');
    }
    // Relationships
    public function originAddress()
    {
        return $this->hasOne(Address::class, 'shipment_id')->where('type', AddressType::ORIGIN());
    }

    public function destinationAddress()
    {
        return $this->hasOne(Address::class, 'shipment_id')->where('type', AddressType::DESTINATION());
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
