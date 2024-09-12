<?php

namespace App\Services;

use App\Models\user\User;
use App\Models\user\UserOtp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function __construct()
    {
    }

    /**
     * @return App\Models\UserOtp
     */
    public function checkAndGetOtp($name, $code, $user_id)
    {
        return UserOtp::where('user_id', $user_id)
            ->where('name', $name)->where('code', $code)
            ->where('expires_at', '>', now())
            ->first();
    }
    public function getUserDetails($id = null, $token = null)
    {
        /** If the user id is not provided, Use the Authenticated User ID */
        $user_id = $id ? $id : Auth::user()->id;
        $user = User::where("id", $user_id)->first();

        $result = [];
        $result["id"] = $user->id;
        $result["email_verified_at"] = $user->email_verified_at;
        $result["email"] = $user->email;
        $result["username"] = $user->username;
        $result["first_name"] = $user->first_name;
        $result["last_name"] = $user->last_name;
        $result["mobile_number"] = $user->mobile_number;
        $result["account_number"] = $user->account_number;
        $result["profile_picture"] = $user->profile_picture;
        $result["profile_picture"] = $user->profile_picture ? Storage::url('uploads/profile-picture/' . $user->profile_picture) : null;
        $result['email_verified'] = $user->email_verified_at ? true : false;

        $token ? $result["bearer_token"] = $token : null;
        return $result;
    }

    public function getUserAccountDetails($id = null, $user = null)
    {

    }
    /**
     * @param array $payload
     * ```php
     * <?php 
     * $payload = [
     *  "username" => "",
     *  "email" => "",
     *  "first_name" => "",
     *  "last_name" => "",
     *  "mobile_number" => "",
     *  "password" => "",
     * ];
     * ```
     * @return User
     */
    public function createUser($payload): User
    {
        $user = new User();
        $user->username = $payload['username'];
        $user->email = $payload['email'];
        $user->first_name = $payload['first_name'];
        $user->last_name = $payload['last_name'];
        $user->mobile_number = $payload['mobile_number'];
        $user->password = $payload['password'];
        $user->account_number = GeneralService::randomChars(10, User::class, "account_number", range(0, 20));
        $user->save();

        return $user;
    }

    public function creditAccount($user_id, $amount, $payment_method, $admin_id, $note)
    {
        $user = User::where("id", $user_id)->first();
        $transactionService = new TransactionService();
        $transactionService->creditUser($amount, $note, $payment_method, $user->id, $admin_id);
    }
}
