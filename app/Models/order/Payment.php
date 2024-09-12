<?php

namespace App\Models\Order;

use App\Models\User\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'reference',
        'amount',
        'order_id',
        'user_id',
        'admin_id',
        'method',
        'note'
    ];
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
