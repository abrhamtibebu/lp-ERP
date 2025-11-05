<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        $products = Product::where('tenant_id', $tenant->id)->get();

        if ($products->isEmpty()) {
            $this->command->warn('Products not found. Please run ProductSeeder first.');
            return;
        }

        $statuses = ['pending', 'in_production', 'completed', 'cancelled'];
        
        $orders = [];
        $orderCount = 15;

        for ($i = 1; $i <= $orderCount; $i++) {
            $product = $products->random();
            $quantity = rand(50, 500);
            
            $orders[] = [
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'color' => $product->color,
                'sku' => $product->sku,
                'status' => $statuses[array_rand($statuses)],
                'notes' => rand(0, 100) > 70 ? 'Special requirements: Custom finish requested' : null,
            ];
        }

        foreach ($orders as $orderData) {
            Order::create($orderData);
        }

        $this->command->info('Orders seeded successfully!');
    }
}

