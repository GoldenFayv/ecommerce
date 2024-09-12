<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductImage extends Model
{
    use HasFactory;
    protected $fillable = ['filename', 'product_id'];

    public function getUrlAttribute(){
        return Storage::url("uploads/products/images/" . $this->filename);
    }
}
