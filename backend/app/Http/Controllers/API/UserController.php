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
            ->with('roles')
            ->get();
        
        return response()->json($users);
    }

    public function assignRole(Request $request, $userId)
    {
        // Only GM can assign roles
        if (!auth()->user()->hasRole('GM')) {
            return response()->json(['message' => 'Only GM can assign roles'], 403);
        }

        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'department' => 'nullable|string|in:HR,Inventory,Production,Logistics,Finance',
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
}
