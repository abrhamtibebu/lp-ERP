<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        $suppliers = [
            [
                'name' => 'Premium Leather Co.',
                'tin_number' => 'TIN-001-2020',
                'products_supplied' => 'Cowhide, Goatskin, Sheepskin',
                'contact_info' => 'Phone: +251-911-234-567, Email: info@premiumleather.et',
            ],
            [
                'name' => 'Quality Hide Suppliers',
                'tin_number' => 'TIN-002-2021',
                'products_supplied' => 'Cowhide, Buffalo Hide',
                'contact_info' => 'Phone: +251-911-345-678, Email: sales@qualityhide.et',
            ],
            [
                'name' => 'Accessories Plus Ltd',
                'tin_number' => 'TIN-003-2021',
                'products_supplied' => 'Zippers, Threads, Buttons, Rivets',
                'contact_info' => 'Phone: +251-911-456-789, Email: contact@accessoriesplus.et',
            ],
            [
                'name' => 'Metal Hardware Supplies',
                'tin_number' => 'TIN-004-2022',
                'products_supplied' => 'Buckles, Rings, Chains, Hooks',
                'contact_info' => 'Phone: +251-911-567-890, Email: metal@hardware.et',
            ],
            [
                'name' => 'Exotic Leather Importers',
                'tin_number' => 'TIN-005-2022',
                'products_supplied' => 'Crocodile, Ostrich, Python',
                'contact_info' => 'Phone: +251-911-678-901, Email: exotic@leather.et',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create([
                'tenant_id' => $tenant->id,
                ...$supplierData,
            ]);
        }

        $this->command->info('Suppliers seeded successfully!');
    }
}

