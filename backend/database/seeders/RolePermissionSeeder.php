<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $roles = [
            ['name' => 'GM', 'display_name' => 'General Manager', 'description' => 'Full system access'],
            ['name' => 'HR', 'display_name' => 'Human Resources', 'description' => 'Employee management'],
            ['name' => 'Inventory Manager', 'display_name' => 'Inventory Manager', 'description' => 'Inventory management'],
            ['name' => 'Production Supervisor', 'display_name' => 'Production Supervisor', 'description' => 'Production workflow'],
            ['name' => 'Logistics', 'display_name' => 'Logistics', 'description' => 'Commercial invoices'],
            ['name' => 'Finance', 'display_name' => 'Finance', 'description' => 'Financial management'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
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
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Assign all permissions to GM role
        $gmRole = Role::where('name', 'GM')->first();
        $allPermissions = Permission::all();
        $gmRole->permissions()->attach($allPermissions);

        // Assign specific permissions to other roles
        $hrRole = Role::where('name', 'HR')->first();
        $hrRole->permissions()->attach(Permission::whereIn('name', [
            'employees.create',
            'employees.edit',
            'employees.view'
        ])->get());

        $inventoryRole = Role::where('name', 'Inventory Manager')->first();
        $inventoryRole->permissions()->attach(Permission::whereIn('name', [
            'inventory.manage'
        ])->get());

        $productionRole = Role::where('name', 'Production Supervisor')->first();
        $productionRole->permissions()->attach(Permission::whereIn('name', [
            'production.manage'
        ])->get());

        $financeRole = Role::where('name', 'Finance')->first();
        $financeRole->permissions()->attach(Permission::whereIn('name', [
            'finance.product_cost',
            'finance.expenses',
            'finance.revenue'
        ])->get());

        $logisticsRole = Role::where('name', 'Logistics')->first();
        $logisticsRole->permissions()->attach(Permission::whereIn('name', [
            'logistics.invoices'
        ])->get());
    }
}

