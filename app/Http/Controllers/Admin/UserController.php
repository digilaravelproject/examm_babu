<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Agar AJAX request hai (Search/Filter), to filtered data return karo
        if ($request->ajax()) {
            $users = $this->getFilteredUsers($request);
            return view('admin.users.partials.users-table', compact('users'))->render();
        }

        // Normal Page Load
        $roles = Role::pluck('name', 'id');
        $users = $this->getFilteredUsers($request);

        return view('admin.users.index', compact('roles', 'users'));
    }

    // Helper function for filtering
    private function getFilteredUsers(Request $request)
    {
        $query = User::with('roles')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $status = $request->status === 'active' ? 1 : 0;
            $query->where('is_active', $status);
        }

        return $query->paginate(10);
    }

    // Toggle Active/Inactive Status
    public function toggleStatus(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User status updated.',
            'new_status' => $user->is_active
        ]);
    }

    // Delete User
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
    }
}
