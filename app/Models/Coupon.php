<?php

namespace App\Models;

use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'discount_amount',
        'type',
        'target',
        'target_id',
        'usage_limit',
        'expires_at',
        'active',
        'admin_id'
    ];

    protected $casts = [
        "expires_at" => "datetime"
    ];
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
