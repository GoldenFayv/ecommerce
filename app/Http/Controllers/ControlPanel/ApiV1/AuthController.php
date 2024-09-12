<?php

namespace App\Http\Controllers\ControlPanel\ApiV1;

use Throwable;
use App\Models\Admin\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;



class AuthController extends Controller
{
    public function login(Request $request, AdminService $adminService)
    {

        $request->validate([
            'email' => 'required_without:username|string|email|max:255',
            'password' => 'required|string|min:8'
        ]);
        try {
            /** @var \App\Models\Admin\Admin */
            $admin = Admin::where('email', $request['email'])->where('deleted_at', null)->first();
            if (!$admin) {
                return $this->failureResponse("Email Does not Exists", null, 404);
            }

            if ($admin->is_active == 0) {
                return $this->failureResponse("Account Deactivated", null, 400);
            }
            if (!Hash::check($request['password'], $admin->password)) {
                return $this->failureResponse("Invalid Credentials", null, 400);
            }
            $credentials = [
                "email" => $admin->email,
                "password" => $request['password'],
            ];
            // Auth::guard("admin")->login($admin);
            $token = Auth::guard("admin")->attempt($credentials);
            $admin_details = $adminService->getProfileDetails($admin->id, $token);
            Admin::where("id", $admin->id)->update([
                "last_login" => now()
            ]);
            return $this->successResponse("Admin Logged in Successfully", $admin_details);
        } catch (Throwable $th) {
            return $this->failureResponse("Internal Server Error", null, 500, $th);
        }
    }

}
