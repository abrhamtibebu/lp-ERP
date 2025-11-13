<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rename GM role to Admin and update display_name
     */
    public function up(): void
    {
        // Rename GM role to Admin
        DB::table('roles')
            ->where('name', 'GM')
            ->update([
                'name' => 'Admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access',
            ]);
    }

    /**
     * Reverse the migrations.
     * Rename Admin role back to GM
     */
    public function down(): void
    {
        // Rename Admin role back to GM
        DB::table('roles')
            ->where('name', 'Admin')
            ->update([
                'name' => 'GM',
                'display_name' => 'General Manager',
                'description' => 'Full system access',
            ]);
    }
};
