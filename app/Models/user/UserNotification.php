<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'message',
        'target',
        'target_id',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
