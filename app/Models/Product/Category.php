<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "slug",
        "filename",
        "admin_id"
    ];
    public function sub_categories()
    {
        return $this->hasMany(\App\Models\Product\SubCategory::class);
    }
    public function getImageAttribute()
    {
        return Storage::url("uploads/subcategory/" . $this->filename);
    }
}
