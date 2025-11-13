<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchStageMovement;
use App\Models\ProductionStage;
use App\Models\WipInventory;
use App\Models\FinishedGood;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    protected BatchService $batchService;
    protected ConsumptionService $consumptionService;

    public function __construct(BatchService $batchService, ConsumptionService $consumptionService)
    {
        $this->batchService = $batchService;
        $this->consumptionService = $consumptionService;
    }

    public function moveBatchToStage(
        Batch $batch,
        int $toStageId,
        int $quantity,
        int $supervisorId,
        ?int $fromStageId = null,
        ?string $notes = null
    ): BatchStageMovement {
        return DB::transaction(function () use ($batch, $toStageId, $quantity, $supervisorId, $fromStageId, $notes) {
            $toStage = ProductionStage::findOrFail($toStageId);
            
            // Check if this is the first movement (before creating the movement record)
            // Deduct raw materials only on the first movement to prevent consuming materials multiple times
            $hasPreviousMovements = $batch->stageMovements()->exists();
            if (!$hasPreviousMovements) {
                $tenant = Tenant::findOrFail($batch->tenant_id);
                try {
                    $this->consumptionService->deductMaterials($batch, $quantity, $tenant->leather_consumption_mode);
                } catch (\Exception $e) {
                    // Log the error for tracking
                    \Log::warning('Failed to deduct materials for batch movement', [
                        'batch_id' => $batch->id,
                        'quantity' => $quantity,
                        'error' => $e->getMessage(),
                    ]);
                    // Re-throw the exception so the user is notified
                    throw $e;
                }
            }
            
            // Create movement record
            $movement = BatchStageMovement::create([
                'tenant_id' => $batch->tenant_id,
                'batch_id' => $batch->id,
                'from_stage_id' => $fromStageId ?? $batch->current_stage_id,
                'to_stage_id' => $toStageId,
                'quantity' => $quantity,
                'supervisor_id' => $supervisorId,
                'notes' => $notes,
            ]);

            // Update WIP inventory
            $this->updateWipInventory($batch, $toStageId, $quantity);

            // If moving from a stage, reduce quantity from that stage
            if ($fromStageId) {
                $this->reduceWipInventory($batch, $fromStageId, $quantity);
            }

            // Update batch current stage
            $batch->update([
                'current_stage_id' => $toStageId,
                'current_quantity' => $batch->current_quantity - $quantity,
            ]);

            // Check if stage is "Goods at Inventory" - move to finished goods
            if ($toStage->name === 'Goods at Inventory') {
                $this->moveToFinishedGoods($batch, $quantity);
            }

            return $movement;
        });
    }

    protected function updateWipInventory(Batch $batch, int $stageId, int $quantity): void
    {
        $wip = WipInventory::where('batch_id', $batch->id)
            ->where('stage_id', $stageId)
            ->first();

        if ($wip) {
            $wip->increment('quantity', $quantity);
        } else {
            WipInventory::create([
                'tenant_id' => $batch->tenant_id,
                'batch_id' => $batch->id,
                'stage_id' => $stageId,
                'quantity' => $quantity,
            ]);
        }
    }

    protected function reduceWipInventory(Batch $batch, int $stageId, int $quantity): void
    {
        $wip = WipInventory::where('batch_id', $batch->id)
            ->where('stage_id', $stageId)
            ->first();

        if ($wip) {
            $wip->decrement('quantity', $quantity);
            if ($wip->quantity <= 0) {
                $wip->delete();
            }
        }
    }

    protected function moveToFinishedGoods(Batch $batch, int $quantity): void
    {
        FinishedGood::create([
            'tenant_id' => $batch->tenant_id,
            'batch_id' => $batch->id,
            'product_id' => $batch->order->product_id,
            'quantity' => $quantity,
            'completed_at' => now(),
        ]);
    }
}

