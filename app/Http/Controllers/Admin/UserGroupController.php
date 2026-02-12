<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str; // Required for random string
use Exception;

class UserGroupController extends Controller
{
    public function index(Request $request)
    {
        $query = UserGroup::query();

        $query->when($request->filled('search'), function ($q) use ($request) {
            $q->where(function ($subQ) use ($request) {
                $subQ->where('name', 'like', '%' . $request->search . '%')
                     ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('is_active', $request->status);
        });

        $query->when($request->filled('visibility'), function ($q) use ($request) {
            $q->where('is_private', $request->visibility);
        });

        $userGroups = $query->latest()
            ->paginate($request->input('perPage', 10))
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.user-groups._table', compact('userGroups'))->render();
        }

        return view('admin.user-groups.index', compact('userGroups'));
    }

    public function store(Request $request)
    {
        // 1. Validation (Code hata diya yahan se)
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:user_groups,name',
            'description' => 'nullable|string|max:1000',
            'status'      => 'required|boolean',
            'visibility'  => 'required|boolean',
        ], [
            'name.unique' => 'This Group Name is already taken. Please choose another.',
        ]);

        DB::beginTransaction();

        try {
            // 2. Auto Generate Unique Code
            $generatedCode = $this->generateUniqueCode();

            // 3. Create
            UserGroup::create([
                'name'        => $validated['name'],
                'code'        => $generatedCode, // Auto Generated Here
                'description' => $validated['description'],
                'is_active'   => $validated['status'],
                'is_private'  => $validated['visibility'],
                'settings'    => []
            ]);

            DB::commit();

            return redirect()->back()->with('successMessage', 'User Group created successfully! Code: ' . $generatedCode);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("UserGroup Store Error: " . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('errorMessage', 'Failed to create User Group. Please try again.');
        }
    }

    public function edit($id)
    {
        try {
            $userGroup = UserGroup::findOrFail($id);

            if (request()->ajax()) {
                return response()->json([
                    'status'      => true,
                    'id'          => $userGroup->id,
                    'name'        => $userGroup->name,
                    'code'        => $userGroup->code, // Bhejenyge taaki user dekh sake, par edit nahi kar payega
                    'description' => $userGroup->description,
                    'status'      => $userGroup->is_active,
                    'visibility'  => $userGroup->is_private,
                    'update_url'  => route('admin.user-groups.update', $userGroup->id)
                ]);
            }

            return redirect()->route('admin.user-groups.index');

        } catch (Exception $e) {
            if (request()->ajax()) {
                return response()->json(['status' => false, 'message' => 'Group not found'], 404);
            }
            return redirect()->route('admin.user-groups.index')->with('errorMessage', 'Group not found.');
        }
    }

    public function update(Request $request, $id)
    {
        $userGroup = UserGroup::findOrFail($id);

        // 1. Validation (Code hata diya, kyunki edit allowed nahi hai)
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', Rule::unique('user_groups')->ignore($userGroup->id)],
            'description' => 'nullable|string|max:1000',
            'status'      => 'required|boolean',
            'visibility'  => 'required|boolean',
        ], [
            'name.unique' => 'This Group Name is already taken.',
        ]);

        DB::beginTransaction();

        try {
            // 2. Update (Sirf allowed fields update honge, CODE nahi)
            $userGroup->update([
                'name'        => $validated['name'],
                // 'code' => ... // Code update nahi kar rahe
                'description' => $validated['description'],
                'is_active'   => $validated['status'],
                'is_private'  => $validated['visibility'],
            ]);

            DB::commit();

            return redirect()->back()->with('successMessage', 'User Group updated successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("UserGroup Update Error (ID: $id): " . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('errorMessage', 'Failed to update User Group.');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $userGroup = UserGroup::findOrFail($id);

            if (method_exists($userGroup, 'users')) $userGroup->users()->detach();
            if (method_exists($userGroup, 'quizSchedules')) $userGroup->quizSchedules()->detach();
            if (method_exists($userGroup, 'examSchedules')) $userGroup->examSchedules()->detach();

            $userGroup->delete();

            DB::commit();

            return redirect()->back()->with('successMessage', 'User Group deleted successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("UserGroup Delete Error (ID: $id): " . $e->getMessage());

            return redirect()->back()->with('errorMessage', 'Unable to delete group. It might be in use.');
        }
    }

    /**
     * Helper to generate unique code like 'ugp_e8kRPF2DfY0'
     */
    private function generateUniqueCode()
    {
        do {
            // 'ugp_' prefix + 11 random alphanumeric characters
            $code = 'ugp_' . Str::random(11);
        } while (UserGroup::where('code', $code)->exists()); // Ensure uniqueness

        return $code;
    }
}
