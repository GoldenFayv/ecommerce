<?php

namespace App\Models;

use App\Enums\FileUploadPaths;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CustomsDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'document_type',
        'file_name'
    ];

    protected $casts = ['file_name' => 'json'];
    // Relationships
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id', 'id');
    }

    public function getFilesAttribute()
    {
        // Return an empty collection if file_name is null or empty
        return collect($this->file_name)->map(function ($file) {
            return Storage::url(FileUploadPaths::CUSTOM_DOCUMENT . "/{$file}");
        });
    }
}
