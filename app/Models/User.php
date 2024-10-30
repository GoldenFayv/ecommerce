<?php

namespace App\Models;

use App\Models\UserOtp;
use Illuminate\Database\Eloquent\Model;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    protected $fillable = ['email', 'password'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    // Implement required methods for JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Your existing relationships
    public function profile()
    {
        return $this->morphTo();
    }

    public function createOtp($name, $expires_at = null)
    {
        if (!$expires_at) {
            $expires_at = now()->addMinutes(10);
        }

        $otp = substr(mt_rand(10003401, 99999999), 0, 6);
        UserOtp::where('name', $name)->where('user_id', $this->id)->delete();
        $create = UserOtp::create([
            'user_id' => $this->id,
            'name' => $name,
            'code' => $otp,
            'expires_at' => $expires_at,
        ]);

        if ($create) {
            return $otp;
        } else {
            return false;
        }
    }
}
