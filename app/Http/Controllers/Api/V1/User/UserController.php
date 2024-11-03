<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\User;
use Illuminate\Support\Env;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;



class UserController extends Controller
{
    public $userService;
    /** @var User $user */
    public $user;

    public function __construct(UserService $userService)  // Inject the service for better testability
    {
        if (Auth::check()) {
            $this->user = Auth::user();
            $this->userService = $userService;
        }
    }

    public function updateProfile(Request $request)
    {
        $this->user->email = $request->email;
        $this->user->save();

        $payload = $request->all();
        $payload['user'] = $this->user;

        return $this->successResponse($this->userService->updateUser($payload));
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
        return $this->successResponse('Sent');
    }
}
