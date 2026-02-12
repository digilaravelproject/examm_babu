<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    /**
     * Display the Role Matrix and User Manager.
     */
    public function index()
    {
        // Cache reset to ensure fresh data
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = Role::where('name', '!=', 'guest')->get();
        $permissions = Permission::all();

        // Group permissions by category (e.g., 'create-post' -> 'post')
        $groupedPermissions = $permissions->groupBy(function ($item) {
            $parts = preg_split('/[-_ ]/', $item->name);
            return end($parts) ?? 'Other';
        });

        return view('admin.roles-permissions.index', compact('roles', 'groupedPermissions'));
    }

    /**
     * Store a new Role with Transaction and Validation.
     */
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name|max:64|regex:/^[a-zA-Z0-9\-]+$/',
        ], [
            'name.regex' => 'Role name should only contain letters, numbers, and dashes.',
        ]);

        DB::beginTransaction(); // Transaction Start

        try {
            $role = Role::create(['name' => strtolower($request->name)]);

            // Optional: Log activity
            if(function_exists('activity')) {
                activity()->log("Created role {$role->name}");
            }

            DB::commit(); // Save Data
            return back()->with('success', 'Role created successfully with secure transaction.');
        } catch (\Exception $e) {
            DB::rollBack(); // Revert changes if error
            Log::error("Role Creation Failed: " . $e->getMessage());
            return back()->with('error', 'Failed to create role. Please try again.');
        }
    }

    /**
     * Assign/Revoke Permission for a ROLE (The Matrix).
     */
    public function updateRolePermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_name' => 'required|exists:permissions,name',
            'status' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            $role = Role::findById($request->role_id);
            $permission = $request->permission_name;

            if ($request->status) {
                $role->givePermissionTo($permission);
            } else {
                $role->revokePermissionTo($permission);
            }

            // Clear Cache immediately
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            DB::commit();
            return response()->json(['message' => 'Role permission updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Role Permission Update Failed: " . $e->getMessage());
            return response()->json(['message' => 'System error occurred.'], 500);
        }
    }

    /**
     * Search for a User to manage their individual permissions.
     * FIXED: Handles missing 'name' column by searching first_name/last_name
     */
    public function searchUser(Request $request)
    {
        $search = $request->get('q');

        // ERROR FIX START:
        // Hum 'name' column direct check nahi kar rahe kyunki wo missing hai.
        // Hum check kar rahe hain: Email OR First Name OR Last Name
        $users = User::where(function($query) use ($search) {
            $query->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            // Agar aapke paas username column hai to niche wali line uncomment karein:
            // ->orWhere('username', 'like', "%{$search}%");
        })
        ->with('roles', 'permissions') // Eager load relationships
        ->limit(10)
        ->get();

        // Frontend ke liye 'name' attribute manually banana padega
        // Kyunki DB me 'name' column nahi hai, lekin VueJS ko 'user.name' chahiye.
        $users->transform(function($user) {
            $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            // Agar full name khali hai to username ya email ka first part use karo
            $user->name = $fullName ?: ($user->username ?? explode('@', $user->email)[0]);
            return $user;
        });

        return response()->json($users);
    }

    /**
     * Get a specific User's permissions (Inherited + Direct).
     */
    public function getUserPermissions($userId)
    {
        try {
            $user = User::findOrFail($userId);

            // Fix for Frontend Name display inside this method too
            $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            $user->name = $fullName ?: ($user->username ?? explode('@', $user->email)[0]);

            $allPermissions = Permission::all();

            // Map permissions to show status
            $data = $allPermissions->map(function ($perm) use ($user) {
                return [
                    'name' => $perm->name,
                    'inherited_from_role' => $user->getPermissionsViaRoles()->contains('name', $perm->name), // True if comes from Role
                    'direct_permission' => $user->hasDirectPermission($perm->name), // True if assigned specifically to user
                    'has_access' => $user->hasPermissionTo($perm->name) // True if either above is true
                ];
            });

            // Grouping logic for Frontend
            $grouped = $data->groupBy(function ($item) {
                $parts = preg_split('/[-_ ]/', $item['name']);
                return end($parts) ?? 'Other';
            });

            return response()->json([
                'user' => $user,
                'user_roles' => $user->getRoleNames(),
                'permissions' => $grouped
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    /**
     * Assign/Revoke Permission DIRECTLY to a User (Override).
     */
    public function updateUserPermission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission_name' => 'required|exists:permissions,name',
            'action' => 'required|in:give,revoke', // 'give' (direct assign) or 'revoke' (remove direct)
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($request->user_id);
            $permission = $request->permission_name;

            if ($request->action === 'give') {
                // User ko direct permission do (bhale hi role mein na ho)
                $user->givePermissionTo($permission);
            } else {
                // Direct permission wapas lo
                $user->revokePermissionTo($permission);
            }

            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            DB::commit();

            return response()->json([
                'message' => 'User permission updated.',
                'is_direct' => $user->hasDirectPermission($permission),
                'has_access' => $user->hasPermissionTo($permission)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("User Permission Override Failed: " . $e->getMessage());
            return response()->json(['message' => 'Failed to update user permission.'], 500);
        }
    }
}
