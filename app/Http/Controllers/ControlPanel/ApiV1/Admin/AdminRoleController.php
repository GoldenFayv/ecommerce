<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\Admin;

use App\Http\Controllers\Controller;
use App\Models\admin\AdminRole;
use App\Models\admin\AdminRolePermission;
use Illuminate\Http\Request;

class AdminRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = AdminRole::with(['role_permissions'])->get();
        $result = array_map(function ($adminRole) {
            return $this->details(adminRole: $adminRole);
        }, $roles->toArray());

        return $this->successResponse("", $result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|unique:admin_roles,name",
            "permissions" => "required|array",
            "permissions.*" => "exists:permissions,id"
        ]);

        $new_role = AdminRole::create([
            "name" => $request['name']
        ]);

        array_map(function ($premission_id) use ($new_role) {
            AdminRolePermission::create([
                "permission_id" => $premission_id,
                "admin_role_id" => $new_role->id
            ]);
        }, $request['permissions']);

        return $this->successResponse("Role Created");
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
            return $this->failureResponse("Role Not Found");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = AdminRole::where("id", $id)->first();
        if ($role) {
            if (!empty($request['permissions'])) {
                $role_id = $role->id;
                // delete old permissions
                AdminRolePermission::where("admin_role_id", $role_id)->delete();
                array_map(function ($premission_id) use ($role_id) {
                    AdminRolePermission::create([
                        "permission_id" => $premission_id,
                        "admin_role_id" => $role_id
                    ]);
                }, $request['permissions']);
            }
            if (!empty($request['name'])) {
                $role->name = $request['name'];
                $role->save();
            }
            return $this->successResponse("Role Updated");
        } else {
            return $this->failureResponse("Role Not Found");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $adminRole = AdminRole::Where("id", $id)->delete();
        return $this->successResponse("Role Deleted");
    }

    public function details($id = null, mixed $adminRole = null)
    {
        if (!$adminRole) {
            $adminRole = AdminRole::with(['role_permissions'])->Where("id", $id)->first()->toArray();
            if (!$adminRole)
                return false;
        }
        return [
            "id" => $adminRole["id"],
            "name" => $adminRole["name"],
            "created_at" => $adminRole["created_at"],
            "permissions" => array_map(function ($role_permission) {
                return [
                    "permission_id" => $role_permission['permission']['id'],
                    "permission_name" => $role_permission['permission']['name'],
                ];
            }, $adminRole["role_permissions"])
        ];
    }
}

