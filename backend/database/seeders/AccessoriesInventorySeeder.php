<?php

namespace Database\Seeders;

use App\Models\AccessoriesInventory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccessoriesInventorySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
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

        $accessories = [
            [
                'name' => 'Zippers - 12 inch',
                'quantity' => 500,
                'unit' => 'pcs',
                'import_invoice_number' => 'INV-2024-001',
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
            ],
            [
                'name' => 'Zippers - 18 inch',
                'quantity' => 300,
                'unit' => 'pcs',
                'import_invoice_number' => 'INV-2024-002',
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
            ],
            [
                'name' => 'Thread - Nylon White',
                'quantity' => 150,
                'unit' => 'spools',
                'import_invoice_number' => 'INV-2024-003',
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
            ],
            [
                'name' => 'Thread - Nylon Black',
                'quantity' => 150,
                'unit' => 'spools',
                'import_invoice_number' => 'INV-2024-003',
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
            ],
            [
                'name' => 'Buttons - Brass Large',
                'quantity' => 1000,
                'unit' => 'pcs',
                'import_invoice_number' => 'INV-2024-004',
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
            ],
            [
                'name' => 'Buttons - Brass Small',
                'quantity' => 2000,
                'unit' => 'pcs',
                'import_invoice_number' => 'INV-2024-004',
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
            ],
            [
                'name' => 'Rivets - Copper',
                'quantity' => 5000,
                'unit' => 'pcs',
                'import_invoice_number' => 'INV-2024-005',
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
            ],
            [
                'name' => 'Buckles - Leather Belt',
                'quantity' => 200,
                'unit' => 'pcs',
                'import_invoice_number' => 'INV-2024-006',
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
            ],
            [
                'name' => 'Rings - D-Ring',
                'quantity' => 800,
                'unit' => 'pcs',
                'import_invoice_number' => 'INV-2024-007',
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
            ],
            [
                'name' => 'Chains - Shoulder Strap',
                'quantity' => 150,
                'unit' => 'pcs',
                'import_invoice_number' => 'INV-2024-008',
                'submitted_by' => $inventoryManager->id,
                'received_by' => $hrManager->id,
            ],
        ];

        foreach ($accessories as $accessoryData) {
            AccessoriesInventory::create([
                'tenant_id' => $tenant->id,
                ...$accessoryData,
            ]);
        }

        $this->command->info('Accessories Inventory seeded successfully!');
    }
}

