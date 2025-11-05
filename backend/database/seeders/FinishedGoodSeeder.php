<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\FinishedGood;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class FinishedGoodSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        $batches = Batch::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->get();

        if ($batches->isEmpty()) {
            $this->command->warn('Completed batches not found. Please run BatchSeeder first.');
            return;
        }

        foreach ($batches as $batch) {
            $order = $batch->order;
            if (!$order || !$order->product) continue;

            // Create finished goods entry
            FinishedGood::create([
                'tenant_id' => $tenant->id,
                'batch_id' => $batch->id,
                'product_id' => $order->product_id,
                'quantity' => $batch->current_quantity,
                'completed_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info('Finished Goods seeded successfully!');
    }
}

