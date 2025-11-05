<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\ProductionStage;
use App\Models\Tenant;
use App\Models\WipInventory;
use Illuminate\Database\Seeder;

class WipInventorySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        $batches = Batch::where('tenant_id', $tenant->id)->get();
        $stages = ProductionStage::where('tenant_id', $tenant->id)->orderBy('sequence')->get();

        if ($batches->isEmpty()) {
            $this->command->warn('Batches not found. Please run BatchSeeder first.');
            return;
        }

        foreach ($batches as $batch) {
            // Create WIP entries for each stage up to the current stage
            $currentStage = $batch->currentStage;
            if (!$currentStage) continue;

            $currentSequence = $currentStage->sequence;
            
            foreach ($stages as $stage) {
                if ($stage->sequence <= $currentSequence) {
                    // Some quantity is in this stage
                    $quantityInStage = rand(0, (int)($batch->current_quantity * 0.3));
                    
                    if ($quantityInStage > 0) {
                        WipInventory::create([
                            'tenant_id' => $tenant->id,
                            'batch_id' => $batch->id,
                            'stage_id' => $stage->id,
                            'quantity' => $quantityInStage,
                        ]);
                    }
                }
            }
        }

        $this->command->info('WIP Inventory seeded successfully!');
    }
}

