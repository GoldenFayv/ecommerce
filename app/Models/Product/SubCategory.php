<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        "category_id",
        "name",
        "filename",
        "slug",
        "admin_id"
    ];

    public function category()
    {
        return $this->belongsTo(\App\Models\Product\Category::class);
    }
    public function getImageAttribute(){
        return Storage::url("uploads/subcategory/" . $this->filename);
    }
}
