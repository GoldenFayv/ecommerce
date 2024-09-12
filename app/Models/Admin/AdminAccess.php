<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAccess extends Model
{
    use HasFactory;
    protected $fillable = ['permission_id', 'access_mode', 'admin_id'];

    public function permission()
    {
        return $this->belongsTo("App\Models\admin\Permission", "permission_id");
    }
}
