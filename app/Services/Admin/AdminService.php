<?php

namespace App\Services\Admin;

use Carbon\Carbon;
use App\Models\Sale\Sale;
use App\Models\User\User;
use App\Models\Sale\Order;
use App\Models\Admin\Admin;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Admin\AccessCode;
use App\Models\admin\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminService
{
    function checkCode($permission_id, $code)
    {
        $code = AccessCode::where("permission_id", $permission_id)->where('admin_id', Auth::guard("admin")->user()->id)->where('code', $code)->first();
        if ($code) {
            $code->delete();
            return true;
        } else {
            return false;
        }
    }
    function sendOtp(Request $request)
    {
        $admin = Auth::guard("admin")->user();
        $permission_code = $request["permission_code"];
        $permission = Permission::where("code", "like", "$permission_code")->first();
        $controller = new Controller();

        if ($permission) {

            // get the supervisor
            $supervisor = $this->getSupervisor($permission->code);
            if (!$supervisor) {
                return $controller->failureResponse("Supervisor not Found");
            }
            // generate code
            $code = strtoupper(Str::random(6));

            $permission_name = $permission->name;
            $admin = Auth::guard("admin")->user();
            AccessCode::create([
                "code" => $code,
                "permission_id" => $permission->id,
                "admin_id" => Auth::guard("admin")->user()->id,
                "expires_at" => now()->addMinutes(15)
            ]);

            $details = $this->getPermissionExtraMessage($permission_code, $request['item_id']);

            if ($details) {

                $controller->sendMail($supervisor->email, $permission_name . " Access Code", "mails.access_code", [
                    "supervisor_name" => $supervisor->first_name,
                    "admin_name" => $admin->first_name . ', ' . $admin->last_name,
                    "permission_name" => $permission->description,
                    "details" => $details,
                    "code" => $code
                ]);
                return $controller->successResponse("Access Code Sent to Supervisor");
            } else {
                return $controller->failureResponse("Item not Found");
            }
        } else {
            return $controller->failureResponse("Permission not Found");
        }
    }

    // get the supervisor for a role
    function getSupervisor($permission)
    {
        $role = "";
        if (preg_match("/payment/", $permission)) {
            $role = "sales-manager";
        } else if (preg_match("/sales/", $permission) || preg_match("/crate/", $permission)) {
            $role = "warehouse-manager";
        } else {
            switch ($permission) {
                case 'credit-user':
                    $role = "sales-manager";
                    break;
            }
        }

        $admin = Admin::whereRaw("admin_role_id = (select id from admin_roles where slug like '$role')")->first();
        if ($admin) {
            return $admin;
        } else {
            $superAdmin = Admin::where("id", 1)->first();
            return $superAdmin;
        }
    }
    function getPermissionExtraMessage($permission_name, $item_id)
    {
        $permission_name = strtolower($permission_name);
        $item_type = request()->get("item_type") ?? null;
        $details = false;
        if (preg_match("/payment/", $permission_name)) {
            $order = Order::where("id", $item_id)->first();
            if ($order) {
                $details = ["Order Reference" => $order->reference];
            }
        } else if (preg_match("/sale/", $permission_name) || preg_match("/crate/", $permission_name)) {
            $reference = null;
            if ($item_type == "reference") {
                $order = Order::where("reference", $item_id)->first();
                if ($order) {
                    $reference = $order->reference;
                }
            } else {
                $sale = Sale::where("id", $item_id)->first();
                if ($sale) {
                    $reference = $sale->order->reference;
                }
            }
            if ($reference) {
                $details = ["Order Reference" => $reference];
                ;
            }
        } else {
            switch ($permission_name) {
                case 'credit-user':
                    $user = User::where("account_number", $item_id)->first();
                    $details = [
                        "First Name" => $user->first_name,
                        "Last Name" => $user->last_name,
                        "Account Number" => $user->account_number,
                    ];
                    break;

                default:
                    # code...
                    break;
            }
        }
        return $details;
    }
    function getPermissionWithRequestMethod(Request $request, $path)
    {
        switch ($request->method()) {
            case 'post':
                return PremissionsWithRequest::POST . ' ' . $path;
            case 'get':
                return PremissionsWithRequest::GET . ' ' . $path;
            case 'patch':
                return PremissionsWithRequest::PATCH . ' ' . $path;
            case 'delete':
                return PremissionsWithRequest::DELETE . ' ' . $path;
        }
    }

    function getProfileDetails($admin_id = null, $token = null)
    {
        /**
         * @var Admin
         */
        $admin = Auth::guard("admin")->user() ?? Admin::where("id", $admin_id)->first();
        $result = $admin;
        $result['last_login'] = (new Carbon($admin->last_login))->format("h:i:s d M, Y");
        $result['profile_picture'] = $admin->profile_picture ? Storage::url('uploads/profile-pictures/' . $admin->profile_picture) : null;
        $token ? $result['bearer_token'] = $token : null;


        $result["permissions"] = $admin->permissions;
        $result['role'] = $admin->isSuperAdmin() ? [
            "name" => "Super Admin"
        ] : ["name" => $admin->role->name, "id" => $admin->role->id];
        return $result;
    }
}
