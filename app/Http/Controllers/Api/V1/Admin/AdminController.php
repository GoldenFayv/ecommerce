<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Config;
use Illuminate\Http\Request;
use App\Models\Admin\AdminRole;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
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
