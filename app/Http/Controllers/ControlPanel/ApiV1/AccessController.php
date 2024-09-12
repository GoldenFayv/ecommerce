<?php

namespace App\Http\Controllers\ControlPanel\ApiV1;

use App\Http\Controllers\Controller;
use App\Services\cpanel\AdminService;
use Illuminate\Http\Request;


class AccessController extends Controller
{
    public function store(Request $request, AdminService $adminService){
        $request->validate([
            "permission_code" => "required|exists:permissions,code",
            "item_id" => "required",
            "details" => "array"
        ]);

        return $adminService->sendOtp($request);
    }

}
