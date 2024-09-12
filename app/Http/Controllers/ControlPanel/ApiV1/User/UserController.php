<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\User;

use App\Models\User\User;

use App\Models\Admin\Admin;
use App\Services\GeneralService;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    public function index(Request $request, UserService $userService)
    {
        // check if this is a wholeseller
        /**
         * @var Admin
         */
        $admin = Auth::guard("admin")->user();
        $users_id = User::pluck("id");

        $result = array_map(function ($id) use ($userService) {
            return $userService->getUserDetails($id);
        }, $users_id->toArray());

        return $this->successResponse("", $result);
    }

    public function update(Request $request, $user_id)
    {
        $user = User::where("id", $user_id)->first();
        if ($user) {
            if (isset($request['status'])) {
                $user->is_active = $request['status'] == "true" ? 1 : 0;
            }
            if (!empty($request['email'])) {
                $user->email = $request['email'];
            }

            if (!empty($request['mobile_number'])) {
                $user->mobile_number = $request['mobile_number'];
            }

            if (!empty($request['first_name'])) {
                $user->first_name = $request['first_name'];
            }

            if (!empty($request['last_name'])) {
                $user->last_name = $request['last_name'];
            }

            if (!empty($request['mobile_number'])) {
                $user->mobile_number = $request['mobile_number'];
            }

            if (!empty($request['company_name'])) {
                $user->company_name = $request['company_name'];
            }

            if (!empty($request['password'])) {
                $user->password = $request['password'];
            }
            if (!empty($request['profile_picture'])) {
                // delete old image
                $old_image = $user->profile_picture;
                Storage::delete("uploads/profile-pictures/" . $old_image);
                $file_name = $this->uploadFile($request['profile_picture'], "profile-pictures");
                $user->profile_picture = $file_name;
            }
            $user->save();
            return $this->successResponse("User Updated");
        } else {
            return $this->failureResponse("User Not Found");
        }
    }
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:255|unique:users',
            'profile_picture' => 'required|image',
            'password' => 'required|string|min:8',
        ]);

        $admin = Auth::guard("admin")->user();
        $validateData["admin_id"] = $admin->id;
        $validateData["email_verified_at"] = now();
        $validateData["account_number"] = generateRef(10, User::class, "account_number");

        $user = User::create($validateData);

        $file_name = $this->uploadFile($request['profile_picture'], "profile-pictures");
        $user->profile_picture = $file_name;
        $user->save();

        // Mail the New User
        $maildata = [
            "name" => $user->first_name,
            "account_number" => $user->account_number,
            "password" => $request["password"],
        ];
        $this->sendMail($user->email, "Account Created", "mails.account_created", $maildata);
        return $this->successResponse("User Created");
    }
    public function destory($user_id)
    {
        /**
         * @var User
         */
        User::where("id", $user_id)->delete();
        ;
        return $this->successResponse("User Deleted");
    }
    public function show(UserService $userService, $user_id)
    {
        $user = $userService->getUserDetails($user_id);
        if ($user) {
            return $this->successResponse("", $user);
        } else {
            return $this->failureResponse("User Not Found");
        }
    }
    public function creditUser(Request $request, UserService $userService, $user_id)
    {
        $validatedData = $request->validate([
            "amount" => "required|numeric",
            "account_number" => "required",
            "payment_method" => "required",
            "comment" => "required"
        ]);

        $user = User::where("account_number", $validatedData["account_number"])->first();
        if ($user) {
            $userService->creditAccount(
                $user->id,
                $validatedData["amount"],
                $validatedData["payment_method"],
                auth("admin")->user()->id,
                $validatedData["comment"],
            );
            return $this->successResponse("");
        } else {
            return $this->failureResponse("User Not Found");
        }
    }
}
