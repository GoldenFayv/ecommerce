<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\UserOtp;
use App\Models\Shipment;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'last_name',
        'first_name',
        'profile_picture',
        'email_verified_at',
        'isAdmin',
        'created_by',
        'mobile_number'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // public function getNameAttribute(){
    //     return $this->first_name . ' ' . $this->last_name;
    // }

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

    public function getNameAttribute()
    {
        return "{$this->last_name} {$this->first_name}";
    }
    public function notifications()
    {
        return $this->hasMany(UserNotification::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function shipment()
    {
        return $this->hasMany(Shipment::class, 'user_id');
    }
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
