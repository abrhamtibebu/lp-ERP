<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCost;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductCostSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        $products = Product::where('tenant_id', $tenant->id)->get();
        $financeManager = User::where('tenant_id', $tenant->id)
            ->where('department', 'Finance')
            ->first();

        if ($products->isEmpty()) {
            $this->command->warn('Products not found. Please run ProductSeeder first.');
            return;
        }

        foreach ($products as $product) {
            // Calculate base cost (roughly 40% of unit price)
            $baseCost = $product->unit_price * 0.4;

            // Randomly lock some costs
            $isLocked = rand(0, 100) > 60; // 40% chance of being locked

            ProductCost::create([
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'cost' => round($baseCost, 2),
                'is_locked' => $isLocked,
                'locked_by' => $isLocked && $financeManager ? $financeManager->id : null,
                'locked_at' => $isLocked ? now()->subDays(rand(1, 30)) : null,
                'notes' => $isLocked ? 'Cost locked for production planning' : null,
            ]);
        }

        $this->command->info('Product Costs seeded successfully!');
    }
}

