<?php

namespace App\Services;

use App\Models\User;
use App\Models\Admin;
use App\Models\Agent;
use App\Enums\UserType;
use App\Models\UserOtp;
use App\Models\Customer;
use App\traits\AdminTrait;
use App\traits\AgentTrait;
use App\traits\CustomerTrait;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Auth;

class UserService
{
    use CustomerTrait, AdminTrait, AgentTrait;

    public $user;
    public function __construct()
    {
        if(Auth::check()){
            $this->user = Auth::user();
        }
    }


    public function checkAndGetOtp($name, $code, $user_id)
    {
        return UserOtp::where('user_id', $user_id)
            ->where('name', $name)->where('code', $code)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function getUserDetails($userId = null, $token = null)
    {
        $userId ??= Auth::id();
        $user = User::find($userId);

        if(!$user){
            throw new CustomException("Invalid User");
        }

        $result = [];
        $result["id"] = $user->id;
            $result["email"] = $user->email;


        // Determine profile type and add relevant details

        switch ($user->profile_type) {
            case Customer::class:
                $result["role"] = UserType::CUSTOMER;
                $result = array_merge($result, $this->getCustomerDetails($user->profile));
                break;

            case Agent::class:
                $result["role"] = UserType::AGENT;
                $result["profile"] = [
                    'agency_name' => $user->profile->agency_name,
                    'phone' => $user->profile->phone,
                    // Add other Agent-specific details here
                ];
                break;

            case Admin::class:
                $result["role"] = UserType::ADMIN;
                $result["profile"] = [
                    'first_name' => $user->profile->first_name,
                    'last_name' => $user->profile->last_name,
                    // Add other Admin-specific details here
                ];
                break;

            default:
                $result["role"] = 'Unknown';
                $result["profile"] = [];
                break;
        }

        // Add token to the result if provided
        if ($token) {
            $result["bearer_token"] = $token;
        }

        return $result;
    }

    public function createUser($payload): User
    {
        switch($payload['type']){
            case UserType::CUSTOMER:
                $model = $this->createCustomer($payload);
                break;
            case UserType::ADMIN:
                $model = $this->createAdmin($payload);
                break;
            case UserType::AGENT:
                $model = $this->createAgent($payload);
                break;
            default:
                throw new CustomException("Invalid User Type");
        }

        $user = new User();
        $user->email = $payload['email'];
        $user->password = $payload['password'];

        $model->user()->save($user);

        return $user;
    }

    public function creditAccount($user_id, $amount, $payment_method, $admin_id, $note)
    {
        $user = User::where("id", $user_id)->first();
        $transactionService = new TransactionService();
        $transactionService->creditUser($amount, $note, $payment_method, $user->id, $admin_id);
    }
}
