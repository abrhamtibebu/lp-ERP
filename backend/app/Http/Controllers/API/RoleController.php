<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Get all roles with their permissions
     * Returns roles with permissions grouped by module
     */
    public function index()
    {
        try {
            // Only Admin can view roles
            if (!auth()->user()->hasRole('Admin')) {
                return response()->json(['message' => 'Only Admin can view roles'], 403);
            }

            $roles = Role::with(['permissions', 'users' => function ($query) {
                $query->wherePivot('tenant_id', auth()->user()->tenant_id);
            }])->get();

            // Get all permissions grouped by module
            $permissions = Permission::orderBy('module')->orderBy('display_name')->get();
            $permissionsByModule = $permissions->groupBy('module');

            return response()->json([
                'roles' => $roles,
                'permissions' => $permissions,
                'permissions_by_module' => $permissionsByModule,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching roles: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);

            return response()->json([
                'error' => 'Failed to fetch roles',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while fetching roles',
            ], 500);
        }
    }

    /**
     * Get a specific role with its permissions and assigned users
     */
    public function show($id)
    {
        try {
            // Only Admin can view roles
            if (!auth()->user()->hasRole('Admin')) {
                return response()->json(['message' => 'Only Admin can view roles'], 403);
            }

            $role = Role::with(['permissions', 'users' => function ($query) {
                $query->wherePivot('tenant_id', auth()->user()->tenant_id);
            }])->findOrFail($id);

            return response()->json($role);
        } catch (\Exception $e) {
            Log::error('Error fetching role: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'role_id' => $id,
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);

            return response()->json([
                'error' => 'Failed to fetch role',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while fetching role',
            ], 500);
        }
    }

    /**
     * Create a new role with permissions
     */
    public function store(Request $request)
    {
        try {
            // Only Admin can create roles
            if (!auth()->user()->hasRole('Admin')) {
                return response()->json(['message' => 'Only Admin can create roles'], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'display_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id',
            ], [
                'name.unique' => 'A role with this name already exists.',
                'name.required' => 'Role name is required.',
                'display_name.required' => 'Display name is required.',
                'permissions.required' => 'At least one permission must be selected.',
                'permissions.array' => 'Permissions must be an array.',
                'permissions.*.exists' => 'One or more selected permissions are invalid.',
            ]);

            return DB::transaction(function () use ($request) {
                // Create the role
                $role = Role::create([
                    'name' => $request->name,
                    'display_name' => $request->display_name,
                    'description' => $request->description ?? null,
                ]);

                // Attach permissions to the role
                if ($request->has('permissions') && is_array($request->permissions)) {
                    $permissionIds = array_map('intval', $request->permissions);
                    $role->permissions()->attach($permissionIds);
                }

                // Load the role with permissions
                $role->load('permissions');

                Log::info('Role created successfully', [
                    'role_id' => $role->id,
                    'role_name' => $role->name,
                    'permissions_count' => $role->permissions->count(),
                    'user_id' => auth()->id(),
                    'tenant_id' => auth()->user()->tenant_id ?? null
                ]);

                return response()->json([
                    'message' => 'Role created successfully',
                    'role' => $role,
                ], 201);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating role: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->except(['permissions']),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);

            return response()->json([
                'error' => 'Failed to create role',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while creating role',
            ], 500);
        }
    }

    /**
     * Update an existing role and its permissions
     */
    public function update(Request $request, $id)
    {
        try {
            // Only Admin can update roles
            if (!auth()->user()->hasRole('Admin')) {
                return response()->json(['message' => 'Only Admin can update roles'], 403);
            }

            $role = Role::findOrFail($id);

            // Prevent editing Admin role (for security)
            if ($role->name === 'Admin') {
                return response()->json([
                    'message' => 'Cannot modify the Admin role for security reasons',
                ], 403);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $id,
                'display_name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'permissions' => 'sometimes|required|array',
                'permissions.*' => 'exists:permissions,id',
            ], [
                'name.unique' => 'A role with this name already exists.',
                'name.required' => 'Role name is required.',
                'display_name.required' => 'Display name is required.',
                'permissions.required' => 'At least one permission must be selected.',
                'permissions.array' => 'Permissions must be an array.',
                'permissions.*.exists' => 'One or more selected permissions are invalid.',
            ]);

            return DB::transaction(function () use ($request, $role) {
                // Update role attributes
                if ($request->has('name')) {
                    $role->name = $request->name;
                }
                if ($request->has('display_name')) {
                    $role->display_name = $request->display_name;
                }
                if ($request->has('description')) {
                    $role->description = $request->description;
                }
                $role->save();

                // Update permissions if provided
                if ($request->has('permissions') && is_array($request->permissions)) {
                    $permissionIds = array_map('intval', $request->permissions);
                    $role->permissions()->sync($permissionIds);
                }

                // Load the role with permissions
                $role->load('permissions');

                Log::info('Role updated successfully', [
                    'role_id' => $role->id,
                    'role_name' => $role->name,
                    'permissions_count' => $role->permissions->count(),
                    'user_id' => auth()->id(),
                    'tenant_id' => auth()->user()->tenant_id ?? null
                ]);

                return response()->json([
                    'message' => 'Role updated successfully',
                    'role' => $role,
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating role: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'role_id' => $id,
                'request_data' => $request->except(['permissions']),
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);

            return response()->json([
                'error' => 'Failed to update role',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while updating role',
            ], 500);
        }
    }

    /**
     * Delete a role
     * Only allowed if no users are assigned to this role
     */
    public function destroy($id)
    {
        try {
            // Only Admin can delete roles
            if (!auth()->user()->hasRole('Admin')) {
                return response()->json(['message' => 'Only Admin can delete roles'], 403);
            }

            $role = Role::findOrFail($id);

            // Prevent deleting Admin role (for security)
            if ($role->name === 'Admin') {
                return response()->json([
                    'message' => 'Cannot delete the Admin role for security reasons',
                ], 403);
            }

            // Check if any users are assigned to this role
            $usersWithRole = $role->users()->wherePivot('tenant_id', auth()->user()->tenant_id)->count();
            
            if ($usersWithRole > 0) {
                return response()->json([
                    'message' => 'Cannot delete role. There are ' . $usersWithRole . ' user(s) assigned to this role. Please reassign users before deleting.',
                    'users_count' => $usersWithRole,
                ], 422);
            }

            return DB::transaction(function () use ($role) {
                // Detach all permissions from the role
                $role->permissions()->detach();
                
                // Delete the role
                $role->delete();

                Log::info('Role deleted successfully', [
                    'role_id' => $id,
                    'role_name' => $role->name,
                    'user_id' => auth()->id(),
                    'tenant_id' => auth()->user()->tenant_id ?? null
                ]);

                return response()->json([
                    'message' => 'Role deleted successfully',
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Error deleting role: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'role_id' => $id,
                'user_id' => auth()->id(),
                'tenant_id' => auth()->user()->tenant_id ?? null
            ]);

            return response()->json([
                'error' => 'Failed to delete role',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while deleting role',
            ], 500);
        }
    }
}

