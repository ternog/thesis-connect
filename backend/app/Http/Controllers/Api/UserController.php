<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->canManageUsers()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = User::with('role');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('department', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('role_id') && $request->role_id) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    public function store(Request $request)
    {
        if (!$request->user()->canManageUsers()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'department' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'department' => $request->department,
            'is_active' => $request->get('is_active', true),
        ]);

        return response()->json($user->load('role'), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load(['role', 'uploadedTheses', 'approvedTheses']));
    }

    public function update(Request $request, User $user)
    {
        if (!$request->user()->canManageUsers() && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'department' => 'nullable|string|max:255',
        ];

        // Only admins can change role and active status
        if ($request->user()->canManageUsers()) {
            $rules['role_id'] = 'sometimes|required|exists:roles,id';
            $rules['is_active'] = 'sometimes|boolean';
        }

        // Only allow password change if provided
        if ($request->has('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        $updateData = $request->only(['name', 'email', 'department']);

        if ($request->user()->canManageUsers()) {
            $updateData = array_merge($updateData, $request->only(['role_id', 'is_active']));
        }

        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json($user->load('role'));
    }

    public function destroy(Request $request, User $user)
    {
        if (!$request->user()->canManageUsers()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function getRoles()
    {
        $roles = Role::orderBy('display_name')->get();
        return response()->json($roles);
    }

    public function approve(Request $request, User $user)
    {
        if (!$request->user()->canManageUsers()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $user->update([
            'is_approved' => true,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        ActivityLog::logActivity(
            "Approved user account: {$user->name}",
            $user,
            $request->user(),
            null,
            'user_approved',
            'user'
        );

        return response()->json([
            'message' => 'User approved successfully.',
            'user' => $user->load('role')
        ]);
    }

    public function pendingApprovals(Request $request)
    {
        if (!$request->user()->canManageUsers()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $pendingUsers = User::with('role')
            ->pendingApproval()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($pendingUsers);
    }

    public function updatePreferences(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'program' => 'nullable|string|max:255',
            'interests' => 'nullable|array',
            'interests.*' => 'string|max:100',
            'department' => 'nullable|string|max:255',
        ]);

        $user->update($request->only(['program', 'interests', 'department']));

        ActivityLog::logActivity(
            "Updated profile preferences",
            $user,
            $user,
            [
                'program' => $request->program,
                'interests' => $request->interests,
            ],
            'profile_updated',
            'user'
        );

        return response()->json([
            'message' => 'Preferences updated successfully.',
            'user' => $user->load('role')
        ]);
    }
}