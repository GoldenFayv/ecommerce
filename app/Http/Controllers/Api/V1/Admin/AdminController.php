<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Admin;
use App\Models\Config;
use Illuminate\Http\Request;
use App\Models\Admin\AdminRole;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct(){
        $admin = Auth::user();

        if($admin->profile_type !== Admin::class){
            abort(403, "Access denied");
        }
    }
    public function listAdminRoles()
    {
        $roles = AdminRole::get(['id', 'name']);

        return $this->successResponse('Admin Roles', $roles);
    }

    public function addConfig(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required',
            'value' => 'required',
        ]);

        Config::create($validated);

        return $this->successResponse('Added');
    }

}
