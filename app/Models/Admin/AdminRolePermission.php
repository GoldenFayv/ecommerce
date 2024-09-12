<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminRolePermission extends Model
{
    use HasFactory;
    protected $fillable = ['permission_id', 'admin_role_id'];

    public function permission()
    {
        return $this->belongsTo("App\Models\Admin\Permission", "permission_id");
    }
}
