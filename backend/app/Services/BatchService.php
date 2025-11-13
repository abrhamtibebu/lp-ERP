<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Order;
use App\Models\CommercialInvoice;
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

        // Update commercial invoice with batch_id if invoice exists
        $invoice = CommercialInvoice::where('order_id', $order->id)
            ->where('tenant_id', $order->tenant_id)
            ->whereNull('batch_id')
            ->first();
        
        if ($invoice) {
            $invoice->update(['batch_id' => $batch->id]);
        } else {
            // Create invoice if it doesn't exist
            $this->createInvoiceFromBatch($order, $batch);
        }

        return $batch;
    }

    protected function createInvoiceFromBatch(Order $order, Batch $batch)
    {
        $productCost = \App\Models\ProductCost::where('product_id', $order->product_id)
            ->where('tenant_id', $order->tenant_id)
            ->first();
        
        $unitPrice = $order->product->unit_price ?? 0;
        if ($productCost) {
            $unitPrice = $productCost->cost;
        }
        
        $totalAmount = $unitPrice * $order->quantity;
        
        $invoiceNumber = 'INV-' . strtoupper(Str::random(8)) . '-' . date('Ymd');
        
        $productDetails = [
            [
                'product_id' => $order->product_id,
                'product_name' => $order->product->product_name,
                'sku' => $order->sku,
                'color' => $order->color,
                'quantity' => $order->quantity,
                'price' => $unitPrice,
            ]
        ];
        
        CommercialInvoice::create([
            'tenant_id' => $order->tenant_id,
            'order_id' => $order->id,
            'batch_id' => $batch->id,
            'invoice_number' => $invoiceNumber,
            'product_details' => $productDetails,
            'total_amount' => $totalAmount,
            'invoice_date' => now()->toDateString(),
            'notes' => 'Auto-generated from batch',
            'created_by' => auth()->id(),
        ]);
    }

    public function generateBatchId(): string
    {
        return 'BATCH-' . strtoupper(Str::random(8)) . '-' . date('Ymd');
    }
}

