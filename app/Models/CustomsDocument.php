<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomsDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'document_type',
        'file_path'
    ];

    // Relationships
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id', 'id');
    }
}
