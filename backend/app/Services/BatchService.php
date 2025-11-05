<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Order;
use App\Models\ProductionStage;
use Illuminate\Support\Str;

class BatchService
{
    public function createBatchFromOrder(Order $order): Batch
    {
        $firstStage = ProductionStage::where('is_active', true)
            ->orderBy('order')
            ->first();

        $batchId = 'BATCH-' . strtoupper(Str::random(8)) . '-' . date('Ymd');

        $batch = Batch::create([
            'tenant_id' => $order->tenant_id,
            'order_id' => $order->id,
            'batch_id' => $batchId,
            'current_stage_id' => $firstStage?->id,
            'status' => 'pending',
            'total_quantity' => $order->quantity,
            'current_quantity' => $order->quantity,
        ]);

        return $batch;
    }

    public function generateBatchId(): string
    {
        return 'BATCH-' . strtoupper(Str::random(8)) . '-' . date('Ymd');
    }
}

