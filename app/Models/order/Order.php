<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        "user_id",
        "admin_id",
        "total",
        "payment_method",
        "delivery_method",
        "sub_total",
        "discount",
        "status",
        "delivery_cost",
        "payment_status",
        "payment_date",
        "reference"
    ];

    protected $casts = [
        "payment_date" => "datetime"
    ];
    public function user()
    {
        return $this->belongsTo(\App\Models\User\User::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class)->with(["product"]);
    }

    public function getDueAttribute()
    {
        $payments = $this->payments;
        if ($payments) {
            $total_payment = $payments->sum("amount");
            $due = $this->total - $total_payment;
            return max($due, 0); // To Avoid Negative Value
        } else {
            return $this->total;
        }
    }
    public function getTotalPaymentAttribute()
    {
        $total_payment = $this->payments->sum("amount");
        return $total_payment;
    }
}
