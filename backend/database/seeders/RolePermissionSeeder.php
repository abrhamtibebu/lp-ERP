<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles (handle Admin role migration scenario)
        // Check if Admin role exists (after migration) or GM role exists (before migration)
        $adminRole = Role::where('name', 'Admin')->first();
        if (!$adminRole) {
            // Check if GM role exists (migration hasn't run yet)
            $gmRole = Role::where('name', 'GM')->first();
            if ($gmRole) {
                // Rename GM to Admin in seeder (migration will handle this, but this ensures it works)
                $gmRole->update([
                    'name' => 'Admin',
                    'display_name' => 'Administrator',
                    'description' => 'Full system access',
                ]);
                $adminRole = $gmRole->fresh();
            } else {
                // Create Admin role if neither exists
                $adminRole = Role::create([
                    'name' => 'Admin',
                    'display_name' => 'Administrator',
                    'description' => 'Full system access',
                ]);
            }
        }
        
        $roles = [
            ['name' => 'HR', 'display_name' => 'Human Resources', 'description' => 'Employee management'],
            ['name' => 'Inventory Manager', 'display_name' => 'Inventory Manager', 'description' => 'Inventory management'],
            ['name' => 'Production Supervisor', 'display_name' => 'Production Supervisor', 'description' => 'Production workflow'],
            ['name' => 'Logistics', 'display_name' => 'Logistics', 'description' => 'Commercial invoices'],
            ['name' => 'Finance', 'display_name' => 'Finance', 'description' => 'Financial management'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['display_name' => $role['display_name'], 'description' => $role['description']]
            );
        }

        // Create Permissions
        $permissions = [
            // Employees
            ['name' => 'employees.create', 'display_name' => 'Create Employees', 'module' => 'employees'],
            ['name' => 'employees.edit', 'display_name' => 'Edit Employees', 'module' => 'employees'],
            ['name' => 'employees.view', 'display_name' => 'View Employees', 'module' => 'employees'],
            
            // Inventory
            ['name' => 'inventory.manage', 'display_name' => 'Manage Inventory', 'module' => 'inventory'],
            
            // Production
            ['name' => 'production.manage', 'display_name' => 'Manage Production', 'module' => 'production'],
            
            // Finance
            ['name' => 'finance.product_cost', 'display_name' => 'Manage Product Costs', 'module' => 'finance'],
            ['name' => 'finance.expenses', 'display_name' => 'Manage Expenses', 'module' => 'finance'],
            ['name' => 'finance.revenue', 'display_name' => 'Manage Revenue', 'module' => 'finance'],
            
            // Logistics
            ['name' => 'logistics.invoices', 'display_name' => 'Manage Invoices', 'module' => 'logistics'],
            
            // Operations
            ['name' => 'operations.manage', 'display_name' => 'Manage Operations', 'module' => 'operations'],
            ['name' => 'operations.approve', 'display_name' => 'Approve Operations', 'module' => 'operations'],
            
            // Reports
            ['name' => 'reports.view', 'display_name' => 'View Reports', 'module' => 'reports'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name'], 'module' => $permission['module']]
            );
        }

        // Assign all permissions to Admin role
        // Refresh the role to ensure we have the latest from database
        $adminRole->refresh();
        $allPermissions = Permission::all();
        // Sync permissions (this will replace existing permissions if any)
        if ($adminRole && $allPermissions->count() > 0) {
            $adminRole->permissions()->sync($allPermissions->pluck('id'));
        }

        // Assign specific permissions to other roles
        $hrRole = Role::where('name', 'HR')->first();
        if ($hrRole) {
            $hrRole->permissions()->syncWithoutDetaching(Permission::whereIn('name', [
                'employees.create',
                'employees.edit',
                'employees.view'
            ])->pluck('id'));
        }

        $inventoryRole = Role::where('name', 'Inventory Manager')->first();
        if ($inventoryRole) {
            $inventoryRole->permissions()->syncWithoutDetaching(Permission::whereIn('name', [
                'inventory.manage'
            ])->pluck('id'));
        }

        $productionRole = Role::where('name', 'Production Supervisor')->first();
        if ($productionRole) {
            $productionRole->permissions()->syncWithoutDetaching(Permission::whereIn('name', [
                'production.manage'
            ])->pluck('id'));
        }

        $financeRole = Role::where('name', 'Finance')->first();
        if ($financeRole) {
            $financeRole->permissions()->syncWithoutDetaching(Permission::whereIn('name', [
                'finance.product_cost',
                'finance.expenses',
                'finance.revenue'
            ])->pluck('id'));
        }

        $logisticsRole = Role::where('name', 'Logistics')->first();
        if ($logisticsRole) {
            $logisticsRole->permissions()->syncWithoutDetaching(Permission::whereIn('name', [
                'logistics.invoices'
            ])->pluck('id'));
        }

        // Create Operations role if it doesn't exist
        $operationsRole = Role::firstOrCreate(
            ['name' => 'Operations'],
            ['display_name' => 'Operations', 'description' => 'Operations management']
        );
        if ($operationsRole) {
            $operationsRole->permissions()->syncWithoutDetaching(Permission::whereIn('name', [
                'operations.manage',
                'operations.approve'
            ])->pluck('id'));
        }

        // Assign reports.view to all roles (reports accessible to all authenticated users)
        $allRoles = Role::all();
        $reportsPermission = Permission::where('name', 'reports.view')->first();
        foreach ($allRoles as $role) {
            $role->permissions()->syncWithoutDetaching([$reportsPermission->id]);
        }
    }
}

