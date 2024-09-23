<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'number_of_packages',
        'weight',
        'length',
        'width',
        'height',
        'shipment_value',
        'insurance',
        'shipment_contents',
        'fragile',
        'hazardous_materials',
        'shipment_id',
        'package_description'
    ];

    // Relationships
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }
}
