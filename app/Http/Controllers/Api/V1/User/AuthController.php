<?php

namespace App\Http\Controllers\Api\V1\User;

use Throwable;
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Http\Request;
use App\Http\Controllers\Otp;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use App\Services\GeneralService;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function register(UserService $userService, GeneralService $generalService, Request $request)
    {

        $validated = $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'mobile_number' => ['required', 'regex:/^\d+$/'],
            'type' => ["required", Rule::in(UserType::getValues())]
        ]);
        DB::beginTransaction();
        try {
            $user = $userService->createUser($validated);

            $credentials = [
                "email" => $user->email,
                "password" => $request['password'],
            ];
            $token = Auth::attempt($credentials);
            // logger($token); die;
            $result = $userService->getUserDetails($user->id, $token);

            $code = $user->createOtp(Otp::EmailVerification);
            $this->sendMail($user->email, "E-Mail Verification", 'mails.email_verification', [
                'name' => $user->first_name,
                'code' => $code
            ]);
            DB::commit();
            return $this->successResponse("User Created", $result, 201);
        } catch (Throwable $th) {
            DB::rollback();
            if($th instanceof CustomException){
                return $this->failureResponse($th->getMessage(), null, $th->getCode());
            }
            return $this->failureResponse("Internal Server Error", null, 500, $th);
        }
    }

    public function login(UserService $userService, Request $request)
    {
        $request->validate([
            'email' => 'required_without:username|string|email|max:255',
            'password' => 'required|string|min:8'
        ]);

        try {
            /** @var User */
            $user = null;
            if (isset($request['email'])) {

                $user = User::where('email', $request->email)->first();
                if (!$user) {
                    return $this->failureResponse("Email Does not Exists", null, 404);
                }
            } else {
                $user = User::where('username', $request['username'])->first();
                if (!$user) {
                    return $this->failureResponse("Username Does not Exists", null, 404);
                }
            }

            // if ($user->is_active == 0) {
            //     return $this->failureResponse("Account Deactivated", null, 400);
            // }
            if (!Hash::check($request['password'], $user->password)) {
                return $this->failureResponse("Invalid Credentials", null, 400);
            }

            $credentials = [
                "email" => $user->email,
                "password" => $request['password'],
            ];
            $token = Auth::attempt($credentials);

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid Credentials',
                ], 401);
            }

            $result = $userService->getUserDetails(token: $token);

            return $this->successResponse("User Logged in Successfully", $result);
        } catch (Throwable $th) {
            if($th instanceof CustomException){
                return $this->failureResponse($th->getMessage(), null, $th->getCode());
            }
            return $this->failureResponse("Internal Server Error", null, 500, $th);
        }
    }

    public function sendEmailOtp()
    {
        try {
            /** @var User */
            $user = Auth::user();
            $code = $user->createOtp(Otp::EmailVerification);
            if (!$code) {
                return $this->failureResponse("Unable to Send Code", null, 500);
            }
            if ($user->email_verified_at) {
                return $this->failureResponse("Email Already Verified", null, 400);
            }

            $this->sendMail($user->email, "E-Mail Verification", 'mails.email_verification', [
                'name' => $user->first_name,
                'code' => $code
            ]);
            return $this->successResponse("Email Verification OTP Sent", null);
        } catch (Throwable $th) {
            return $this->failureResponse("Internal Server Error", null, 500, $th);
        }
    }

    public function verifyEmail(Request $request, UserService $userService)
    {
        $request->validate([
            'otp' => 'required|string'
        ]);
        try {
            /** @var User */
            $user = Auth::user();
            $check_otp = $userService->checkAndGetOtp(Otp::EmailVerification, $request['otp'], $user->id);

            if (!$check_otp) {
                return $this->failureResponse("Invalid OTP", null, 400);
            }

            $check_otp->delete();
            $user->email_verified_at = now();
            $update = $user->save();

            if ($update) {
                return $this->successResponse("Email Successfully Verified");
            } else {
                return $this->failureResponse("Email Not Verified");
            }
        } catch (Throwable $th) {
            return $this->failureResponse("Internal Server Error", null, 500, $th);
        }
    }
    // ACCOUNT OPERATION START
    public function sendActivateOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        try {

            $user = User::where('email', $request['email'])->where('deleted_at', null)->first();
            if (!$user) {
                return $this->failureResponse("Email Does Not Exists", null, 404);
            }

            if (!$user->is_active = 1) {
                return $this->failureResponse("Account Already Active", null, 400);
            }
            $code = $user->createOtp(Otp::AccountActivation);
            if (!$code) {
                return $this->failureResponse("Unable to Send Code", null, 500);
            }

            $this->sendMail($user->email, "Account Activation", 'mails.account_activation', [
                'username' => $user->first_name,
                'code' => $code
            ]);
            return $this->successResponse("Account Activation OTP Sent", null);
        } catch (Throwable $th) {
            return $this->failureResponse("Internal Server Error", null, 500, $th);
        }
    }
    public function activateAccount(Request $request, UserService $userService)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
        ]);
        try {
            $user = User::where('email', $request['email'])->where('deleted_at', null)->first();
            if (!$user) {
                return $this->failureResponse("Email Does Not Exists", null, 404);
            }
            if (!$user->is_active = 1) {
                return $this->failureResponse("Account Already Active", null, 400);
            }
            $check_otp = $userService->checkAndGetOtp(Otp::AccountActivation, $request['otp'], $user->id);

            if (!$check_otp) {
                return $this->failureResponse("Invalid OTP", null, 400);
            }

            $check_otp->delete();
            $user->is_active = 1;
            $user->save();

            return $this->successResponse("User Account Activated");
        } catch (Throwable $th) {
            return $this->failureResponse("Internal Server Error", null, 500, $th);
        }
    }
    public function deactivateAccount()
    {
        try {
            /** @var User */
            $user = Auth::user();
            $user->is_active = 0;
            $user->save();

            $user->tokens()->delete();
            return $this->successResponse("User Account Deactivated");
        } catch (Throwable $th) {
            return $this->failureResponse("Internal Server Error", null, 500, $th);
        }
    }

    public function requestDelete()
    {

        try {
            /** @var User */
            $user = Auth::user();

            $code = $user->createOtp(Otp::AccountDeletion);
            if (!$code) {
                return $this->failureResponse("Unable to Send Code", null, 500);
            }

            $this->sendMail($user->email, "Account Deletion", 'mails.account_delete', [
                'username' => $user->first_name,
                'code' => $code
            ]);
            return $this->successResponse("Account Deletion OTP Sent", null);
        } catch (Throwable $th) {
            return $this->failureResponse("Internal Server Error", null, 500, $th);
        }
    }

    public function deleteAccount(Request $request, UserService $userService)
    {
        $request->validate([
            'otp' => 'required',
        ]);
        try {
            /** @var User */
            $user = Auth::user();

            $check_otp = $userService->checkAndGetOtp(Otp::AccountDeletion, $request['otp'], $user->id);
            if (!$check_otp) {
                return $this->failureResponse("Invalid OTP", null, 400);
            }
            $user->deleted_at = now();
            $user->save();
            $user->tokens()->delete();
            $check_otp->delete();

            return $this->successResponse("Account Deleted", null);
        } catch (Throwable $th) {
            return $this->failureResponse("Internal Server Error", null, 500, $th);
        }
    }
    // ACCOUNT OPERATION END

    // PASSWORD OPERATION START
    public function sendPasswordLink(Request $request)
    {
        $request->validate([
            "email" => "required|email"
        ]);
        try {
            $user = User::where('email', $request['email'])->first();
            if (!$user) {
                return $this->failureResponse("Email Does not Exist");
            }

            $token = Password::createToken($user);
            $link = route('password.reset', ['token' => $token, "email={$user->email}"]);

            $this->sendMail($user->email, "Reset Password", "mails.reset_password", [
                "link" => $link
            ]);
            return $this->successResponse("Password Reset Link Sent to your mail");
        } catch (Throwable $th) {
            return $this->failureResponse("Internal Server Error", null, 500, $th);
        }
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|string|min:8'
        ]);
        try {
            /** @var User */
            $user = Auth::user();

            if (!(Hash::check($request['old_password'], $user->password))) {
                return $this->failureResponse("The Old Password Is not Valid", null, 400);
            }

            $user->password = Hash::make($request['new_password']);
            $update = $user->save();

            if ($update) {
                return $this->successResponse("Password Successfully Changed");
            } else {

                return $this->failureResponse("Unable to Change Password");
            }
        } catch (Throwable $th) {
            return $this->failureResponse("Internal Server Error", null, 500, $th);
        }
    }


    // PASSWORD OPERATION END
}
