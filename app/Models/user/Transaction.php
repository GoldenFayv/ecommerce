<?php

namespace App\Models\User;

use App\Models\Order\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'type',
        'description',
        'user_id',
        'payment_id',
        'reference'
    ];
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
