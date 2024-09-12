<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\Admin;

use App\Http\Controllers\Controller;
use App\Models\admin\AdminRole;
use App\Models\admin\AdminRolePermission;
use App\Models\admin\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::get();
        $result = array_map(function ($permission) {
            return $this->details(permission: $permission);
        }, $permissions->toArray());

        return $this->successResponse("", $result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|unique:permissions,name",
        ]);

        $new_role = Permission::create([
            "name" => $request['name']
        ]);

        return $this->successResponse("Permission Created");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = $this->details(id: $id);
        if ($role) {
            return $this->successResponse("", $role);
        } else {
            return $this->failureResponse("Permission Not Found");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permission = Permission::Where("id", $id)->first();
        if ($permission) {
            $permission->delete();
            return $this->successResponse("Permission Deleted");
        } else {
            return $this->failureResponse("Permission Not Found");
        }
    }

    public function details($id = null, mixed $permission = null)
    {
        if (!$permission) {
            $permission = Permission::Where("id", $id)->first();
            if (!$permission)
                return false;
        }
        return [
            "id" => $permission["id"],
            "description" => $permission["description"],
            "name" => $permission["name"],
            "created_at" => $permission["created_at"],
        ];
    }
}
