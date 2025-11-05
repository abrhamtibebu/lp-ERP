<?php

namespace Database\Seeders;

use App\Models\CommercialInvoice;
use App\Models\Revenue;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class RevenueSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        $invoices = CommercialInvoice::where('tenant_id', $tenant->id)->get();

        if ($invoices->isEmpty()) {
            $this->command->warn('Commercial Invoices not found. Please run CommercialInvoiceSeeder first.');
            return;
        }

        foreach ($invoices as $invoice) {
            // Create revenue entry for each invoice
            Revenue::create([
                'tenant_id' => $tenant->id,
                'commercial_invoice_id' => $invoice->id,
                'amount' => $invoice->total_amount,
                'revenue_date' => $invoice->invoice_date,
                'description' => 'Revenue from invoice ' . $invoice->invoice_number,
            ]);
        }

        $this->command->info('Revenues seeded successfully!');
    }
}

