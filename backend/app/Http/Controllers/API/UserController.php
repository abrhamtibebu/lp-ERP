<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->with(['roles', 'permissions'])
            ->get();
        
        return response()->json($users);
    }

    public function assignRole(Request $request, $userId)
    {
        // Only Admin can assign roles
        if (!auth()->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Only Admin can assign roles'], 403);
        }

        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'department' => 'nullable|string|in:HR,Inventory,Production,Logistics,Finance,Operations',
        ]);

        $user = User::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($userId);

        $role = Role::findOrFail($request->role_id);

        // Remove existing roles for this tenant
        $user->roles()->wherePivot('tenant_id', auth()->user()->tenant_id)->detach();

        // Assign new role
        $user->roles()->attach($role->id, [
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        // Update department if provided
        if ($request->has('department')) {
            $user->update(['department' => $request->department]);
        }

        return response()->json($user->load('roles'));
    }

    public function changePassword(Request $request, $userId)
    {
        // Only Admin can change other users' passwords
        if (!auth()->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Only Admin can change user passwords'], 403);
        }

        $request->validate([
            'new_password' => 'required|min:8|confirmed',
        ], [
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'new_password.min' => 'The new password must be at least 8 characters.',
        ]);

        $user = User::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($userId);

        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }

    public function updateApproverAccess(Request $request, $userId)
    {
        // Only Admin can update approver access
        if (!auth()->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Only Admin can update approver access'], 403);
        }

        $request->validate([
            'can_approve' => 'required|boolean',
        ]);

        $user = User::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($userId);

        // Get or create operations.approve permission
        $permission = \App\Models\Permission::firstOrCreate(
            ['name' => 'operations.approve'],
            [
                'display_name' => 'Operations Approve',
                'module' => 'operations',
            ]
        );
        
        // Ensure the permission has the module set (in case it was created without it)
        if (!$permission->module) {
            $permission->update(['module' => 'operations']);
        }

        if ($request->can_approve) {
            // Grant permission - check if already exists
            $existingPermission = $user->permissions()
                ->where('permissions.id', $permission->id)
                ->wherePivot('tenant_id', auth()->user()->tenant_id)
                ->first();
            
            if (!$existingPermission) {
                $user->permissions()->attach($permission->id, [
                    'tenant_id' => auth()->user()->tenant_id,
                ]);
            }
        } else {
            // Revoke permission
            $user->permissions()->wherePivot('tenant_id', auth()->user()->tenant_id)
                ->detach($permission->id);
        }

        return response()->json([
            'message' => 'Approver access updated successfully',
            'user' => $user->load('roles', 'permissions'),
        ]);
    }
}
