<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'phone',
        'email',
        'street',
        'city',
        'state',
        'state_abbr',
        'country',
        'country_abbr',
        'postal_code',
        'type',
        'latitude',
        'longitude',
        'customer_id',
    ];

    /**
     * The user associated with the address.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Drop-off points associated with this address.
     */
    public function dropOffPoints()
    {
        return $this->hasMany(DropOffPoint::class, 'address_id');
    }
}
