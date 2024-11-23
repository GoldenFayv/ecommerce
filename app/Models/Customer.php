<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        "first_name",
        "last_name",
        "mobile_number",
        "address",
        "shipper_code",
        "user_id"
    ];

    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
    public function user()
    {
        return $this->morphOne(User::class, 'profile');
    }

    public function addresses(){
        return $this->hasMany(Address::class, 'customer_id');
    }

}
