<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * Class UserController
 * Fully restored logic with Mobile support and Deep Deletion.
 */
class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        // Handle AJAX request for Search/Filter/Pagination
        if ($request->ajax()) {
            $users = $this->getFilteredUsers($request);
            return view('admin.users.partials.users-table', compact('users'))->render();
        }

        // Standard Page Load
        $roles = Role::pluck('name', 'id');
        $users = $this->getFilteredUsers($request);

        return view('admin.users.index', compact('roles', 'users'));
    }

    /**
     * Helper: Filter users based on Request parameters.
     */
    private function getFilteredUsers(Request $request)
    {
        $query = User::with('roles')->latest();

        // Multi-column Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Role-based Filter
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Active/Inactive Status Filter
        if ($request->filled('status')) {
            $status = $request->status === 'active' ? 1 : 0;
            $query->where('is_active', $status);
        }

        return $query->paginate(10);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::pluck('name', 'name');

        // Active groups only
        $userGroups = UserGroup::select(['id', 'name'])
            ->where('is_active', 1)
            ->get();

        return view('admin.users.create', compact('roles', 'userGroups'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // 1. Validation (Outside Try-Catch for proper error display)
        $validated = $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'nullable|string|max:255',
            'user_name'   => 'required|string|max:255|unique:users,user_name',
            'email'       => 'required|email|max:255|unique:users,email',
            'mobile'      => 'nullable|string|max:15|unique:users,mobile',
            'password'    => 'required|min:8',
            'role'        => 'required|exists:roles,name',
            'user_groups' => 'nullable|array',
            'is_active'   => 'nullable',
            'verify_email'=> 'nullable',
        ]);

        DB::beginTransaction();
        try {
            // Create User Instance
            $user = new User();
            $user->first_name = $validated['first_name'];
            $user->last_name = $validated['last_name'];
            $user->user_name = $validated['user_name'];
            $user->email = $validated['email'];
            $user->mobile = $validated['mobile'];
            $user->password = Hash::make($validated['password']);
            $user->is_active = $request->has('is_active') ? 1 : 0;
            $user->email_verified_at = $request->has('verify_email') ? Carbon::now() : null;

            $user->save();

            // Assign Role
            if(isset($validated['role'])) {
                $user->assignRole($validated['role']);
            }

            // Sync Groups with Pivot Data (Important: joined_at)
            if (!empty($request->user_groups)) {
                $user->userGroups()->syncWithPivotValues($request->user_groups, [
                    'joined_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', "New user '{$user->user_name}' created successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating user: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show form to edit user.
     */
    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'name');
        $userRole = $user->roles->pluck('name')->first();
        $userGroups = UserGroup::select(['id', 'name'])->where('is_active', 1)->get();

        // Get currently selected groups IDs
        $selectedGroups = $user->userGroups()->pluck('user_groups.id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'userRole', 'userGroups', 'selectedGroups'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        // 1. VALIDATION FIRST (Critical Fix: Must be outside try-catch to show UI errors)
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'user_name'  => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'mobile'     => ['nullable', 'string', 'max:15', Rule::unique('users')->ignore($user->id)],
            'role'       => 'required|exists:roles,name',
            'user_groups'=> 'nullable|array',
            'user_groups.*' => 'exists:user_groups,id',
            'password'   => 'nullable|min:8',
        ]);

        DB::beginTransaction();
        try {
            // Update Basic Details
            $user->first_name = $validated['first_name'];
            $user->last_name  = $validated['last_name'];
            $user->user_name  = $validated['user_name'];
            $user->email      = $validated['email'];
            $user->mobile     = $validated['mobile'];

            // Handle Password Update
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            // Status Management
            $oldStatus = $user->is_active;
            $newStatus = $request->has('is_active') ? 1 : 0;

            // If user is being deactivated, kill their sessions
            if ($oldStatus == 1 && $newStatus == 0) {
                if (config('session.driver') == 'database') {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                }
            }
            $user->is_active = $newStatus;

            // Handle Email Verification Logic
            if ($request->has('verify_email') && is_null($user->email_verified_at)) {
                $user->email_verified_at = Carbon::now();
            }

            $user->save();

            // Sync Roles
            $user->syncRoles([$validated['role']]);

            // FIX: User Group Mapping
            // Using syncWithPivotValues to ensure pivot columns are filled
            $groups = $request->input('user_groups', []);

            if (count($groups) > 0) {
                // 'joined_at' add kar rahe hain taaki SQL error na aaye
                $user->userGroups()->syncWithPivotValues($groups, [
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $user->userGroups()->detach();
            }

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', "User '{$user->user_name}' updated successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Update Failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Toggle Active Status via AJAX.
     */
    public function toggleStatus(User $user)
    {
        if (Auth::id() == $user->id) {
            return response()->json(['success' => false, 'message' => 'You cannot deactivate yourself!']);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        // Kill session if deactivated
        if (! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => "User '{$user->user_name}' status updated.",
            'new_status' => $user->is_active,
        ]);
    }

    /**
     * Deep Delete User (Detailed Version).
     */
    public function destroy(User $user)
    {
        if (Auth::id() == $user->id) {
            return response()->json(['success' => false, 'message' => 'Self-deletion is not allowed.'], 403);
        }

        DB::beginTransaction();
        try {
            // 1. Delete Exam & Quiz Sessions (Detailed check)
            if (method_exists($user, 'practiceSessions')) {
                $user->practiceSessions()->forceDelete();
            }
            if (method_exists($user, 'quizSessions')) {
                $user->quizSessions()->forceDelete();
            }
            if (method_exists($user, 'examSessions')) {
                $user->examSessions()->forceDelete();
            }

            // 2. Delete Payments & Subscriptions
            if (method_exists($user, 'payments')) {
                $user->payments()->forceDelete();
            }
            if (method_exists($user, 'subscriptions')) {
                $user->subscriptions()->forceDelete();
            }

            // 3. Detach Groups & Roles
            if (method_exists($user, 'userGroups')) {
                $user->userGroups()->detach();
            }
            $user->roles()->detach();
            $user->permissions()->detach();

            // 4. Manual Table Clean-up (Raw Queries for safety)
            DB::table('transactions')->where('payable_type', '=', 'App\Models\User')
                ->where('payable_id', '=', $user->id)->delete();

            DB::table('wallets')->where('holder_type', '=', 'App\Models\User')
                ->where('holder_id', '=', $user->id)->delete();

            DB::table('sessions')->where('user_id', '=', $user->id)->delete();

            // 5. Activity Logs (Optional cleanup)
            // DB::table('activity_log')->where('causer_id', $user->id)->delete();

            // 6. Final Hard Delete
            $user->forceDelete();

            DB::commit();

            return response()->json(['success' => true, 'message' => "User '{$user->user_name}' deleted permanently."]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Unable to Delete: '.$e->getMessage()
            ], 500);
        }
    }
}
