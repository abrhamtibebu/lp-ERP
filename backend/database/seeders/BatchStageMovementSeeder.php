<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\BatchStageMovement;
use App\Models\ProductionStage;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class BatchStageMovementSeeder extends Seeder
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
            $currentStage = $batch->currentStage;
            if (!$currentStage) continue;

            $currentSequence = $currentStage->sequence;
            
            // Create movement history for stages up to current
            for ($seq = 1; $seq <= $currentSequence; $seq++) {
                $stage = $stages->where('sequence', $seq)->first();
                if (!$stage) continue;

                $quantity = rand((int)($batch->total_quantity * 0.2), (int)($batch->total_quantity * 0.5));
                
                BatchStageMovement::create([
                    'tenant_id' => $tenant->id,
                    'batch_id' => $batch->id,
                    'from_stage_id' => $seq > 1 ? $stages->where('sequence', $seq - 1)->first()?->id : null,
                    'to_stage_id' => $stage->id,
                    'quantity' => $quantity,
                    'supervisor_id' => null, // Could link to user if needed
                    'notes' => 'Batch moved to ' . $stage->stage_name,
                ]);
            }
        }

        $this->command->info('Batch Stage Movements seeded successfully!');
    }
}

