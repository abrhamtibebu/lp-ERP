<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create or get super-admin tenant from .env
        $superAdminTenantName = env('SUPER_ADMIN_TENANT_NAME', 'Main Company');
        $superAdminTenantSlug = env('SUPER_ADMIN_TENANT_SLUG', 'main-company');
        
        $superAdminTenant = Tenant::firstOrCreate(
            ['slug' => $superAdminTenantSlug],
            [
                'name' => $superAdminTenantName,
                'leather_consumption_mode' => 'formula',
                'settings' => []
            ]
        );

        // Create super-admin user from .env
        $superAdminEmail = env('SUPER_ADMIN_EMAIL', 'admin@example.com');
        $superAdminPassword = env('SUPER_ADMIN_PASSWORD', 'admin123');
        $superAdminName = env('SUPER_ADMIN_NAME', 'Super Admin');

        // Get Admin role (handles both fresh install and migration scenarios)
        // Migration will rename GM to Admin, so check for both
        $adminRole = Role::where('name', 'Admin')->first();
        if (!$adminRole) {
            $adminRole = Role::where('name', 'GM')->first();
        }
        if (!$adminRole) {
            // Create Admin role if it doesn't exist (fresh install scenario)
            $adminRole = Role::create([
                'name' => 'Admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access',
            ]);
        }
        
        $superAdmin = User::firstOrCreate(
            ['email' => $superAdminEmail],
            [
                'name' => $superAdminName,
                'password' => Hash::make($superAdminPassword),
                'tenant_id' => $superAdminTenant->id,
                'department' => 'Administration',
                'position' => 'Super Admin',
                'employed_on' => now(),
            ]
        );

        // Assign Admin role to super-admin
        if (!$superAdmin->roles()->wherePivot('tenant_id', $superAdminTenant->id)->where('role_id', $adminRole->id)->exists()) {
            $superAdmin->roles()->attach($adminRole->id, ['tenant_id' => $superAdminTenant->id]);
        }

        $this->command->info('Super Admin created successfully!');
        $this->command->info("Email: {$superAdminEmail}");
        $this->command->info("Password: {$superAdminPassword}");
        $this->command->info('');

        // Create a demo tenant (for demo data)
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo-company'],
            [
                'name' => 'Demo Leather Company',
                'leather_consumption_mode' => 'formula',
                'settings' => []
            ]
        );

        // Get all roles (check for Admin first, fallback to GM for migration scenario)
        $adminRoleForDemo = Role::where('name', 'Admin')->first();
        if (!$adminRoleForDemo) {
            $adminRoleForDemo = Role::where('name', 'GM')->first();
        }
        $hrRole = Role::where('name', 'HR')->first();
        $inventoryRole = Role::where('name', 'Inventory Manager')->first();
        $productionRole = Role::where('name', 'Production Supervisor')->first();
        $logisticsRole = Role::where('name', 'Logistics')->first();
        $financeRole = Role::where('name', 'Finance')->first();

        // Create users for each role
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'department' => 'Administration',
                'position' => 'Administrator',
                'employed_on' => now()->subYears(2),
                'emergency_contact' => 'Emergency: +1234567890',
                'role' => $adminRoleForDemo,
            ],
            [
                'name' => 'HR Manager',
                'email' => 'hr@example.com',
                'password' => Hash::make('password123'),
                'department' => 'HR',
                'position' => 'HR Manager',
                'employed_on' => now()->subYear(),
                'emergency_contact' => 'Emergency: +1234567891',
                'role' => $hrRole,
            ],
            [
                'name' => 'Inventory Manager',
                'email' => 'inventory@example.com',
                'password' => Hash::make('password123'),
                'department' => 'Inventory',
                'position' => 'Inventory Manager',
                'employed_on' => now()->subMonths(8),
                'emergency_contact' => 'Emergency: +1234567892',
                'role' => $inventoryRole,
            ],
            [
                'name' => 'Production Supervisor',
                'email' => 'production@example.com',
                'password' => Hash::make('password123'),
                'department' => 'Production',
                'position' => 'Production Supervisor',
                'employed_on' => now()->subMonths(6),
                'emergency_contact' => 'Emergency: +1234567893',
                'role' => $productionRole,
            ],
            [
                'name' => 'Logistics Officer',
                'email' => 'logistics@example.com',
                'password' => Hash::make('password123'),
                'department' => 'Logistics',
                'position' => 'Logistics Officer',
                'employed_on' => now()->subMonths(4),
                'emergency_contact' => 'Emergency: +1234567894',
                'role' => $logisticsRole,
            ],
            [
                'name' => 'Finance Manager',
                'email' => 'finance@example.com',
                'password' => Hash::make('password123'),
                'department' => 'Finance',
                'position' => 'Finance Manager',
                'employed_on' => now()->subYear(),
                'emergency_contact' => 'Emergency: +1234567895',
                'role' => $financeRole,
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['tenant_id' => $tenant->id])
            );

            // Assign role to user with tenant_id
            if (!$user->roles()->wherePivot('tenant_id', $tenant->id)->where('role_id', $role->id)->exists()) {
                $user->roles()->attach($role->id, ['tenant_id' => $tenant->id]);
            }
        }

        $this->command->info('Users created successfully!');
        $this->command->info('Login Credentials:');
        $this->command->info('==================');
        foreach ($users as $userData) {
            $this->command->info($userData['email'] . ' / password123');
        }
    }
}

