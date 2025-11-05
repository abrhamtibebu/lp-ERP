<?php

namespace Database\Seeders;

use App\Models\LeatherInventory;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeatherInventorySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        $suppliers = Supplier::where('tenant_id', $tenant->id)->get();
        $inventoryManager = User::where('tenant_id', $tenant->id)
            ->where('department', 'Inventory')
            ->first();
        $hrManager = User::where('tenant_id', $tenant->id)
            ->where('department', 'HR')
            ->first();

        if (!$inventoryManager || !$hrManager) {
            $this->command->warn('Users not found. Please run UserSeeder first.');
            return;
        }

        $inventories = [
            [
                'leather_name' => 'Premium Cowhide - Grade A',
                'brand_make' => 'Premium Leather Co.',
                'supplier_id' => $suppliers->where('name', 'Premium Leather Co.')->first()?->id ?? $suppliers->first()->id,
                'purchase_date' => now()->subMonths(2),
                'quantity_sqft' => 500.00,
                'consumption_reduction' => 0.00,
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
                'delivered_to' => 'Production Floor A',
            ],
            [
                'leather_name' => 'Goatskin - Brown',
                'brand_make' => 'Premium Leather Co.',
                'supplier_id' => $suppliers->where('name', 'Premium Leather Co.')->first()?->id ?? $suppliers->first()->id,
                'purchase_date' => now()->subMonths(1),
                'quantity_sqft' => 300.00,
                'consumption_reduction' => 45.50,
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
                'delivered_to' => 'Production Floor B',
            ],
            [
                'leather_name' => 'Sheepskin - Natural',
                'brand_make' => 'Premium Leather Co.',
                'supplier_id' => $suppliers->where('name', 'Premium Leather Co.')->first()?->id ?? $suppliers->first()->id,
                'purchase_date' => now()->subWeeks(2),
                'quantity_sqft' => 250.00,
                'consumption_reduction' => 0.00,
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
                'delivered_to' => 'Production Floor A',
            ],
            [
                'leather_name' => 'Cowhide - Black',
                'brand_make' => 'Quality Hide Suppliers',
                'supplier_id' => $suppliers->where('name', 'Quality Hide Suppliers')->first()?->id ?? $suppliers->first()->id,
                'purchase_date' => now()->subWeeks(1),
                'quantity_sqft' => 400.00,
                'consumption_reduction' => 120.75,
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
                'delivered_to' => 'Production Floor B',
            ],
            [
                'leather_name' => 'Buffalo Hide - Thick',
                'brand_make' => 'Quality Hide Suppliers',
                'supplier_id' => $suppliers->where('name', 'Quality Hide Suppliers')->first()?->id ?? $suppliers->first()->id,
                'purchase_date' => now()->subDays(5),
                'quantity_sqft' => 600.00,
                'consumption_reduction' => 0.00,
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
                'delivered_to' => 'Production Floor A',
            ],
        ];

        foreach ($inventories as $inventoryData) {
            LeatherInventory::create([
                'tenant_id' => $tenant->id,
                ...$inventoryData,
            ]);
        }

        $this->command->info('Leather Inventory seeded successfully!');
    }
}

