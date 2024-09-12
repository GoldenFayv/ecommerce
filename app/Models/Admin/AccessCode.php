<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessCode extends Model
{
    use HasFactory;
    protected $fillable = [
        "permission_id",
        "admin_id",
        "code",
        "expires_at"
    ];
}
