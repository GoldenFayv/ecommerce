<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\Admin;

use App\Models\Admin\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\GeneralService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{

    public function index(Request $request)
    {
        $admins = Admin::where("id", "!=", Auth::guard("admin")->user()->id)->get();
        $result = $admins->map(fn($admin) => $this->details($admin->id));

        return $this->successResponse("", $result);
    }
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:admins',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'profile_picture' => 'required|image',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:admin_roles,id'
        ]);

        $admin = new Admin();
        $admin->email = $request['email'];
        $admin->first_name = $request['first_name'];
        $admin->last_name = $request['last_name'];
        $admin->admin_role_id = $request['role_id'];
        $admin->chat_id = 1; //
        $admin->password = Hash::make($request['password']);
        // ensure the id generated does not exists
        $file_name = $this->uploadFile($request['profile_picture'], "profile-pictures");
        $admin->profile_picture = $file_name;
        $admin->save();

        // Mail the New admin
        $maildata = [
            "name" => $admin->first_name,
            "email" => $admin->email,
            "password" => $request["password"],
        ];
        $this->sendMail($admin->email, "Account Created", "mails.admin_account_created", $maildata);
        return $this->successResponse("Admin Created");
    }
    public function update(Request $request, $admin_id)
    {
        $admin = Admin::where("id", $admin_id)->first();
        if ($admin) {
            if (!empty($request['email'])) {
                $admin->email = $request['email'];
            }

            if (!empty($request['first_name'])) {
                $admin->first_name = $request['first_name'];
            }

            if (!empty($request['last_name'])) {
                $admin->last_name = $request['last_name'];
            }

            if (!empty($request['role_id'])) {
                $admin->admin_role_id = $request['role_id'];
            }

            if (!empty($request['password'])) {
                $admin->password = $request['password'];
            }
            if (!empty($request['profile_picture'])) {
                // delete old image
                $old_image = $admin->profile_picture;
                Storage::delete("uploads/profile-pictures/" . $old_image);
                $file_name = $this->uploadFile($request['profile_picture'], "profile-pictures");
                $admin->profile_picture = $file_name;
            }
            $admin->save();
            return $this->successResponse("Admin Updated");
        } else {
            return $this->failureResponse("Admin Not Found");
        }
    }
    public function show($admin_id)
    {
        $admin = $this->details($admin_id);
        if ($admin) {
            return $this->successResponse("", $admin);
        } else {
            return $this->failureResponse("Admin Not Found");
        }
    }
    public function destory($admin_id)
    {
        /**
         * @var Admin
         */
        Admin::where("id", $admin_id)->delete();
        ;
        return $this->successResponse("Admin Deleted");
    }
    private function details($id)
    {
        $admin = Admin::where("id", $id)->first();
        if (!$admin) {
            return false;
        }

        return [
            "id" => $admin->id,
            "profile_picture" => Storage::url('uploads/profile-pictures/' . $admin->profile_picture),
            "first_name" => $admin->first_name,
            "last_name" => $admin->last_name,
            "name" => trim($admin->last_name . ', ' . $admin->first_name, ", "),
            "email" => $admin->email,
            "status" => $admin->is_active == 1 ? true : false,
            "chat_id" => $admin->chat_id,
            "mobile_number" => $admin->mobile_number,
            "role" => $admin->isSuperAdmin() ? ["name" => "Super Admin"] : ["name" => $admin->role->name, "id" => $admin->role->id],
        ];
    }
}
