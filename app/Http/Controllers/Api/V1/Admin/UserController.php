<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\CustomException;
use App\Models\User\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\UserService;
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
            'isAdmin' => 'required|boolean',
            // 'admin_role_id' => ['required_if:isAdmin,'.true, 'exists:admin_roles,id']
        ]);
        $validated['password'] =  Str::random(12);
        $validated['created_by'] = $this->user->id;
        $user = $this->userService->createUser(payload: $validated);

        $this->sendMail($user->email, "Account Creation", 'mails.account_creation', [
            'name' => $user->first_name,
            'password' => $validated['password']
        ]);

        return successResponse('User Successfully Created');
    }

    public function updateUser(Request $request, $userId)
    {
        // Validate incoming request
        $validated = $request->validate([
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $userId, // Allow updating if it's the same user's email
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
        ]);

        // Retrieve the user
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Update user fields (only if present)
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['first_name'])) {
            $user->first_name = $validated['first_name'];
        }
        if (isset($validated['last_name'])) {
            $user->last_name = $validated['last_name'];
        }

        // Handle password update
        if (isset($validated['password'])) {
            $user->password = $validated['password'];  // Hash the new password if provided
        }

        // Save updated user data
        $user->save();

        // Optionally send email to notify user about account update
        Mail::send('mails.account_updated', [
            'name' => $user->first_name,
            'email' => $user->email
        ], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Account Updated');
        });

        return $this->successResponse("Updated");
    }

    public function listUsers()
    {
        $users = User::where('isAdmin', false)->get();

        return $users->map(fn($user) => $this->userService->getUserDetails($user->id));
    }

    public function deleteUser($userId){
        $user = User::find($userId);
        if (!$user) {
            throw new CustomException('User not found');
        }

        $user->delete();

        return $this->successResponse('Deleted');
    }
}
