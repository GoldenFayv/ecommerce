<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'expires_at', 'discount_percent'];

    protected $casts = ['expires_at' => 'dateTime', 'discout_value' => 'float'];

    public function getDiscountedValue($total_amount)
    {
        $discount_percent = $this->discount_percent/100;
        $discount_value = $discount_percent * $total_amount;
        return $total_amount - $discount_value;
    }
}
