<?php

namespace App\Models\Admin;

use App\Models\Coupon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'profile_picture',
        'last_login',
        'is_active',
        'password'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function rolePermissions()
    {
        return $this->role()->first()['role_permissions'];
    }

    public function isSuperAdmin()
    {
        return $this->id === 1;
    }

    public function getPermissionsAttribute()
    {
        if ($this->isSuperAdmin()) {
            return ["*"];
        } else {
            $rolePermissions = $this->isSuperAdmin() ? [] : $this->rolePermissions()->toArray();
            $rolePermissionCodes = array_column(array_column($rolePermissions, "permission"), "code");
            return $rolePermissionCodes;
        }
    }

    public function canAccess($permissionId = null, $code = null)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($code) {
            $checkRolePermission = $this->role->role_permissions->where("code", $code)->first();
            return $checkRolePermission ? true : false;
        } else {
            $accesses = $this->accesses()->get()->pluck("id")->toArray();
            return in_array($permissionId, $accesses);
        }

    }

    public function role()
    {
        return $this->belongsTo("App\Models\Admin\AdminRole", "admin_role_id")->with(["role_permissions"]);
    }
    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
