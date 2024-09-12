<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        "order_id",
        "product_id",
        "total",
        "sub_total",
        "discount",
        "product_price",
        "qty",
    ];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function product()
    {
        return $this->belongsTo(\App\Models\Product\Product::class)->with(['images']);
    }
}
