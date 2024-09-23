<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\AdminRole;

class AdminController extends Controller
{
    public function listAdminRoles()
    {
        $roles = AdminRole::get(['id', 'name']);

        return $this->successResponse('Admin Roles', $roles);
    }
}
