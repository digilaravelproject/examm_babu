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
 * * Handles Admin User Management including Deep Deletion, AJAX Filtering,
 * and Role-based access control.
 */
class UserController extends Controller
{
    /**
     * Display a listing of the users.
     * Supports AJAX for dynamic search and filtering.
     *
     * @return \Illuminate\View\View|string
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
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
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
                    ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        // Role-based Filter (Spatie Permission Scope)
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
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = Role::pluck('name', 'name');
        $userGroups = UserGroup::select(['id', 'name'])->where('is_active', 1)->get();

        return view('admin.users.create', compact('roles', 'userGroups'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'user_name' => 'required|string|unique:users,user_name',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|exists:roles,name',
            'user_groups' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_name' => $request->user_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => $request->has('is_active') ? 1 : 0,
                'email_verified_at' => $request->has('verify_email') ? Carbon::now() : null,
            ]);

            // Assign Role & Groups
            $user->assignRole($request->role);
            if ($request->filled('user_groups')) {
                $user->userGroups()->sync($request->user_groups);
            }

            DB::commit();

            return redirect()->route('admin.users.index')->with('success', 'User added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error: '.$e->getMessage())->withInput();
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
        $selectedGroups = $user->userGroups()->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'userRole', 'userGroups', 'selectedGroups'));
    }

    /**
     * Update the specified user and handle session management.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|exists:roles,name',
        ]);

        DB::beginTransaction();
        try {
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->user_name = $request->user_name;
            $user->email = $request->email;

            // Optional Password Update
            if ($request->filled('password')) {
                $request->validate(['password' => 'min:8']);
                $user->password = Hash::make($request->password);
            }

            // Status & Session Management
            $newStatus = $request->has('is_active') ? 1 : 0;
            if ($user->is_active == 1 && $newStatus == 0) {
                // If user deactivated, kill all active database sessions
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
            $user->is_active = $newStatus;

            $user->save();

            // Sync Roles & Groups
            $user->syncRoles([$request->role]);
            $user->userGroups()->sync($request->user_groups ?? []);

            DB::commit();

            return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Update Failed: '.$e->getMessage());
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
            'message' => 'User status updated successfully.',
            'new_status' => $user->is_active,
        ]);
    }

    /**
     * Deep Delete User and all associated application data.
     * Ported and enhanced from nearcraft UserCrudController.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        if (Auth::id() == $user->id) {
            return response()->json(['success' => false, 'message' => 'Self-deletion is not allowed.'], 403);
        }

        DB::beginTransaction();
        try {
            // 1. Force Delete Session-based activities (Old Logic)
            if (method_exists($user, 'practiceSessions')) {
                $user->practiceSessions()->forceDelete();
            }
            if (method_exists($user, 'quizSessions')) {
                $user->quizSessions()->forceDelete();
            }
            if (method_exists($user, 'examSessions')) {
                $user->examSessions()->forceDelete();
            }

            // 2. Clear Financial & Subscription Data
            if (method_exists($user, 'payments')) {
                $user->payments()->forceDelete();
            }
            if (method_exists($user, 'subscriptions')) {
                $user->subscriptions()->forceDelete();
            }

            // 3. Detach Relations
            if (method_exists($user, 'userGroups')) {
                $user->userGroups()->detach();
            }
            $user->roles()->detach();
            $user->permissions()->detach();

            // 4. Manual Table Clean-up (Transactions, Wallets, Sessions)
            DB::table('transactions')->where('payable_type', 'App\Models\User')
                ->where('payable_id', $user->id)->delete();

            DB::table('wallets')->where('holder_type', 'App\Models\User')
                ->where('holder_id', $user->id)->delete();

            DB::table('sessions')->where('user_id', $user->id)->delete();

            // 5. Final Permanent Deletion
            $user->forceDelete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'User and all associated data purged successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to Delete: '.$e->getMessage(),
            ], 500);
        }
    }
}
