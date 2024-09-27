<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\user\User;
use Illuminate\Support\Env;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;



class UserController extends Controller
{

    public function updateProfile(Request $request)
    {
        /** @var User */
        $user = Auth::user();
        if (!empty($request['first_name']))
            $user->first_name = $request['first_name'];

        if (!empty($request['last_name']))
            $user->last_name = $request['last_name'];

        if (!empty($request['mobile_number']))
            $user->mobile_number = $request['mobile_number'];

        if (!empty($request['profile_picture'])) {
            $filename = $this->uploadFile($request['profile_picture'], 'profile_pictures');
            $user->profile_picture = $filename;
        }
        $user->save();

        return $this->successResponse("User Profile Updated");
    }
    public function getProfile(UserService $userService)
    {
        $userDetails = $userService->getUserDetails();
        return successResponse("User Profile Retrieved", $userDetails);
    }

    public function make_complaint(Request $request)
    {
        $request->validate([
            'subject' => 'required',
            'message' => 'required',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg'
        ]);
        $this->sendMail(Env::get('SUPPORT_EMAIL'), 'Complaint', 'mail.complaint', []);
       return $this->successResponse('');
    }
}
