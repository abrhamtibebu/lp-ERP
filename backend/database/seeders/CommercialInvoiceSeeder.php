<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\CommercialInvoice;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommercialInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        $orders = Order::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->get();
        $batches = Batch::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->get();
        $logisticsUser = User::where('tenant_id', $tenant->id)
            ->where('department', 'Logistics')
            ->first();

        if (($orders->isEmpty() && $batches->isEmpty()) || !$logisticsUser) {
            $this->command->warn('Orders/Batches or Logistics user not found.');
            return;
        }

        $invoiceCounter = 1;

        // Create invoices for completed orders
        foreach ($orders->take(5) as $order) {
            $totalAmount = $order->quantity * $order->product->unit_price;
            
            CommercialInvoice::create([
                'tenant_id' => $tenant->id,
                'order_id' => $order->id,
                'batch_id' => null,
                'invoice_number' => 'INV-' . str_pad($invoiceCounter++, 6, '0', STR_PAD_LEFT),
                'product_details' => [
                    [
                        'product_name' => $order->product->product_name,
                        'color' => $order->color,
                        'sku' => $order->sku,
                        'quantity' => $order->quantity,
                        'unit_price' => $order->product->unit_price,
                    ]
                ],
                'total_amount' => $totalAmount,
                'invoice_date' => now()->subDays(rand(1, 60)),
                'notes' => 'Commercial invoice for order #' . $order->id,
                'created_by' => $logisticsUser->id,
            ]);
        }

        // Create invoices for completed batches
        foreach ($batches->take(5) as $batch) {
            $order = $batch->order;
            if (!$order) continue;

            $totalAmount = $batch->current_quantity * $order->product->unit_price;
            
            CommercialInvoice::create([
                'tenant_id' => $tenant->id,
                'order_id' => $order->id,
                'batch_id' => $batch->id,
                'invoice_number' => 'INV-' . str_pad($invoiceCounter++, 6, '0', STR_PAD_LEFT),
                'product_details' => [
                    [
                        'product_name' => $order->product->product_name,
                        'color' => $order->color,
                        'sku' => $order->sku,
                        'quantity' => $batch->current_quantity,
                        'unit_price' => $order->product->unit_price,
                    ]
                ],
                'total_amount' => $totalAmount,
                'invoice_date' => now()->subDays(rand(1, 60)),
                'notes' => 'Commercial invoice for batch ' . $batch->batch_id,
                'created_by' => $logisticsUser->id,
            ]);
        }

        $this->command->info('Commercial Invoices seeded successfully!');
    }
}

