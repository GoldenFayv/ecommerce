<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentZone extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'country_code',
        'description',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
