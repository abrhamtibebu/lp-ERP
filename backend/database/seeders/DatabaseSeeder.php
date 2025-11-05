<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core setup
            RolePermissionSeeder::class,
            ProductionStageSeeder::class,
            UserSeeder::class,
            
            // Basic entities
            SupplierSeeder::class,
            ProductSeeder::class,
            FixedAssetSeeder::class,
            
            // Inventory
            LeatherInventorySeeder::class,
            AccessoriesInventorySeeder::class,
            
            // Product costs
            ProductCostSeeder::class,
            
            // Production workflow
            OrderSeeder::class,
            BatchSeeder::class,
            WipInventorySeeder::class,
            BatchStageMovementSeeder::class,
            FinishedGoodSeeder::class,
            
            // Finance
            ExpenseSeeder::class,
            
            // Logistics
            CommercialInvoiceSeeder::class,
            RevenueSeeder::class,
        ]);
    }
}
