<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "name",
        "slug",
        "status",
        "admin_id",
        "is_active",
        "selling_price",
        "original_price",
        "sub_category_id",
        "description",
        "delivery_cost",
        "quantity",
        "discount_percent"
    ];

    public function images()
    {
        return $this->hasMany("App\Models\Product\ProductImage", "product_id");
    }
    public function getFirstImageAttribute()
    {
        if ($this->images->count() > 0) {
            return $this->images[0]->url;
        } else {
            return null;
        }
    }

    public function admin()
    {
        return $this->belongsTo("App\Models\admin\Admin", "admin_id");
    }
    public function category()
    {
        return $this->belongsTo(\App\Models\Product\Category::class);
    }
    public function sub_category()
    {
        return $this->belongsTo(\App\Models\Product\SubCategory::class);
    }
    public function is_out_of_stock()
    {
        return $this->available_crates == 0;
    }

}
