<?php

namespace Database\Seeders;

use App\Models\FixedAsset;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class FixedAssetSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        $assets = [
            [
                'description' => 'Industrial Leather Cutting Machine',
                'purchase_year' => '2020-01-15',
                'depreciation' => 15000.00,
                'notes' => 'Used for cutting leather sheets into patterns',
            ],
            [
                'description' => 'Sewing Machine - Juki 1541',
                'purchase_year' => '2020-03-20',
                'depreciation' => 3500.00,
                'notes' => 'Heavy-duty sewing machine for leather',
            ],
            [
                'description' => 'Embossing Machine',
                'purchase_year' => '2021-06-10',
                'depreciation' => 8000.00,
                'notes' => 'For creating patterns and logos on leather',
            ],
            [
                'description' => 'Leather Skiving Machine',
                'purchase_year' => '2021-08-15',
                'depreciation' => 5500.00,
                'notes' => 'Used to thin leather edges',
            ],
            [
                'description' => 'Hydraulic Press Machine',
                'purchase_year' => '2022-02-05',
                'depreciation' => 12000.00,
                'notes' => 'For shaping and molding leather products',
            ],
            [
                'description' => 'Delivery Van',
                'purchase_year' => '2022-05-12',
                'depreciation' => 25000.00,
                'notes' => 'Toyota Hiace for product delivery',
            ],
            [
                'description' => 'Office Furniture Set',
                'purchase_year' => '2020-01-01',
                'depreciation' => 5000.00,
                'notes' => 'Desks, chairs, and filing cabinets',
            ],
            [
                'description' => 'Computer Equipment',
                'purchase_year' => '2023-01-10',
                'depreciation' => 8000.00,
                'notes' => 'Workstations and servers for ERP system',
            ],
        ];

        foreach ($assets as $assetData) {
            FixedAsset::create([
                'tenant_id' => $tenant->id,
                ...$assetData,
            ]);
        }

        $this->command->info('Fixed Assets seeded successfully!');
    }
}

