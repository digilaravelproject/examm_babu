<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class RolePermissionController extends Controller
{
    public function index()
    {
        // Guest role ko matrix me nahi dikhayenge
        $roles = Role::where('name', '!=', 'guest')->get();

        // Saari permissions fetch karo
        $permissions = Permission::all();

        $groupedPermissions = $permissions->groupBy(function($item) {
            return explode(' ', $item->name)[1] ?? 'Other';
        });

        return view('admin.roles-permissions.index', compact('roles', 'groupedPermissions'));
    }

    public function assignPermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required',
            'permission_name' => 'required',
            'status' => 'required|boolean',
        ]);

        $role = Role::findById($request->role_id);
        $permissionName = $request->permission_name;
        $user = Auth::user();

        if ($request->status) {
            // Assign
            if (!$role->hasPermissionTo($permissionName)) {
                $role->givePermissionTo($permissionName);

                // Log Activity
                activity()
                    ->causedBy($user)
                    ->performedOn($role)
                    ->withProperties(['permission' => $permissionName])
                    ->log("Assigned permission '{$permissionName}'");
            }
        } else {
            // Revoke
            if ($role->hasPermissionTo($permissionName)) {
                $role->revokePermissionTo($permissionName);

                // Log Activity
                activity()
                    ->causedBy($user)
                    ->performedOn($role)
                    ->withProperties(['permission' => $permissionName])
                    ->log("Revoked permission '{$permissionName}'");
            }
        }

        return response()->json(['message' => 'Updated successfully']);
    }
}
