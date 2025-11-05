<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Order;
use App\Models\ProductionStage;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        $orders = Order::where('tenant_id', $tenant->id)
            ->whereIn('status', ['pending', 'in_production'])
            ->get();
        $stages = ProductionStage::where('tenant_id', $tenant->id)->get();

        if ($orders->isEmpty()) {
            $this->command->warn('Orders not found. Please run OrderSeeder first.');
            return;
        }

        if ($stages->isEmpty()) {
            $this->command->warn('Production stages not found. Please run ProductionStageSeeder first.');
            return;
        }

        $statuses = ['in_progress', 'on_hold', 'completed'];
        $batchCounter = 1;

        foreach ($orders as $order) {
            // Create 1-3 batches per order
            $batchCount = rand(1, 3);
            
            for ($i = 0; $i < $batchCount; $i++) {
                $batchQuantity = (int)($order->quantity / $batchCount);
                $currentQuantity = rand((int)($batchQuantity * 0.3), $batchQuantity);
                $status = $statuses[array_rand($statuses)];
                
                // Select a random stage (earlier stages for in_progress, later for completed)
                if ($status === 'completed') {
                    $stage = $stages->last();
                } elseif ($status === 'on_hold') {
                    $stage = $stages->random();
                } else {
                    $stage = $stages->where('sequence', '<=', rand(1, $stages->count() / 2))->random();
                }

                Batch::create([
                    'tenant_id' => $tenant->id,
                    'order_id' => $order->id,
                    'batch_id' => 'BATCH-' . str_pad($batchCounter++, 4, '0', STR_PAD_LEFT),
                    'current_stage_id' => $stage->id,
                    'status' => $status,
                    'total_quantity' => $batchQuantity,
                    'current_quantity' => $currentQuantity,
                ]);
            }
        }

        $this->command->info('Batches seeded successfully!');
    }
}

