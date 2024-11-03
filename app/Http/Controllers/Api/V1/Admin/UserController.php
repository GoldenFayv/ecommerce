<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserType;
use App\Models\Customer;
use App\Models\User\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public $userService;
    public $user;

    public function __construct(UserService $userService)  // Inject the service for better testability
    {
        if (Auth::check()) {
            $this->user = Auth::user();
            if (!$this->user->isAdmin) {
                throw new CustomException("Access Denied", 403);
            }
            $this->userService = $userService;
        }
    }
    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile_number' => ['required', 'regex:/^\d+$/'],
            'isAdmin' => 'required|boolean',
            'user_type' => ['required', Rule::in(UserType::getValues())]
            // 'admin_role_id' => ['required_if:isAdmin,'.true, 'exists:admin_roles,id']
        ]);
        $validated['password'] =  Str::random(12);
        $validated['created_by'] = $this->user->id;
        $user = $this->userService->createUser(payload: $validated);

        $this->sendMail($user->email, "Account Creation", 'mails.account_creation', [
            'name' => $user->first_name,
            'password' => $validated['password'],
            'email' =>  $user->email,
        ]);

        return successResponse('User Successfully Created');
    }

    public function updateUser(Request $request, $userId)
    {
        // Validate incoming request
        $request->validate([
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $userId, // Allow updating if it's the same user's email
            'mobile_number' => 'sometimes|regex:/^\d+$/'
        ]);

        // Retrieve the user
        $user = User::find($userId);

        if (!$user) {
            return $this->failureResponse('User not found');
        }

        // Save updated user data
        $user->email = $request->email;
        $user->save();

        if ($user->isDirty('email')) {
            // Optionally send email to notify user about account update
            $this->sendMail($user->email, "Account Update", 'mails.account_update', [
                'name' => $user->first_name,
                // 'password' => $validated['password']
            ]);
        }

        $payload = $request->all();
        $payload['user'] = $user;

        return $this->successResponse($this->userService->updateUser($payload));
    }

    public function listUsers()
    {
        $users = User::where('profile_type', Customer::class)->get();

        return $users->map(fn($user) => $this->userService->getUserDetails($user->id));
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            throw new CustomException('User not found');
        }

        $user->delete();

        return $this->successResponse('Deleted');
    }
}
