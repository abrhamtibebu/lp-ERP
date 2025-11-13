<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Assign Admin role to admin@example.com user
     */
    public function up(): void
    {
        // Find the user
        $user = User::where('email', 'admin@example.com')->first();
        
        if (!$user) {
            return; // User doesn't exist, skip
        }

        // Find Admin role (check for Admin first, then GM for migration compatibility)
        $adminRole = Role::where('name', 'Admin')->first();
        if (!$adminRole) {
            $adminRole = Role::where('name', 'GM')->first();
        }
        
        if (!$adminRole) {
            // Create Admin role if it doesn't exist
            $adminRole = Role::create([
                'name' => 'Admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access',
            ]);
        }

        // Get all tenants this user belongs to
        $tenantIds = DB::table('role_user')
            ->where('user_id', $user->id)
            ->distinct()
            ->pluck('tenant_id')
            ->filter()
            ->toArray();

        // If user has no tenant assignments, use their tenant_id
        if (empty($tenantIds) && $user->tenant_id) {
            $tenantIds = [$user->tenant_id];
        }

        // If still no tenant, create a default tenant assignment
        if (empty($tenantIds)) {
            // Get or create a default tenant
            $defaultTenant = DB::table('tenants')->first();
            if ($defaultTenant) {
                $tenantIds = [$defaultTenant->id];
            }
        }

        // Remove all existing role assignments for this user
        DB::table('role_user')
            ->where('user_id', $user->id)
            ->delete();

        // Assign Admin role to user for each tenant
        foreach ($tenantIds as $tenantId) {
            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $adminRole->id,
                'tenant_id' => $tenantId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Find the user
        $user = User::where('email', 'admin@example.com')->first();
        
        if (!$user) {
            return;
        }

        // Find Admin role
        $adminRole = Role::where('name', 'Admin')->orWhere('name', 'GM')->first();
        
        if ($adminRole) {
            // Remove Admin role assignment
            DB::table('role_user')
                ->where('user_id', $user->id)
                ->where('role_id', $adminRole->id)
                ->delete();
        }
    }
};
