<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropOffPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_number',
        'status',
        'address_id',
        'location_code',
    ];


    /**
     * Address associated with this drop-off point.
     */
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }
}
