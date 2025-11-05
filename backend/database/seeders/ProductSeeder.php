<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        $products = [
            [
                'product_name' => 'Classic Leather Wallet',
                'color' => 'Brown',
                'sku' => 'WLT-BRN-001',
                'weight_kg' => 0.15,
                'unit_price' => 250.00,
                'consumption_formula' => '0.5 sqft per unit',
                'description' => 'Premium brown leather wallet with multiple card slots',
            ],
            [
                'product_name' => 'Classic Leather Wallet',
                'color' => 'Black',
                'sku' => 'WLT-BLK-001',
                'weight_kg' => 0.15,
                'unit_price' => 250.00,
                'consumption_formula' => '0.5 sqft per unit',
                'description' => 'Premium black leather wallet with multiple card slots',
            ],
            [
                'product_name' => 'Leather Handbag',
                'color' => 'Tan',
                'sku' => 'HBG-TAN-001',
                'weight_kg' => 0.85,
                'unit_price' => 1200.00,
                'consumption_formula' => '3.5 sqft per unit',
                'description' => 'Elegant tan leather handbag with adjustable strap',
            ],
            [
                'product_name' => 'Leather Handbag',
                'color' => 'Red',
                'sku' => 'HBG-RED-001',
                'weight_kg' => 0.85,
                'unit_price' => 1200.00,
                'consumption_formula' => '3.5 sqft per unit',
                'description' => 'Stylish red leather handbag with adjustable strap',
            ],
            [
                'product_name' => 'Leather Belt',
                'color' => 'Brown',
                'sku' => 'BLT-BRN-001',
                'weight_kg' => 0.25,
                'unit_price' => 350.00,
                'consumption_formula' => '1.2 sqft per unit',
                'description' => 'Genuine leather belt with brass buckle',
            ],
            [
                'product_name' => 'Leather Belt',
                'color' => 'Black',
                'sku' => 'BLT-BLK-001',
                'weight_kg' => 0.25,
                'unit_price' => 350.00,
                'consumption_formula' => '1.2 sqft per unit',
                'description' => 'Genuine leather belt with brass buckle',
            ],
            [
                'product_name' => 'Leather Briefcase',
                'color' => 'Black',
                'sku' => 'BFC-BLK-001',
                'weight_kg' => 1.5,
                'unit_price' => 2500.00,
                'consumption_formula' => '5.0 sqft per unit',
                'description' => 'Professional black leather briefcase with combination lock',
            ],
            [
                'product_name' => 'Leather Jacket',
                'color' => 'Brown',
                'sku' => 'JCK-BRN-001',
                'weight_kg' => 2.2,
                'unit_price' => 4500.00,
                'consumption_formula' => '12.0 sqft per unit',
                'description' => 'Classic brown leather jacket with zipper',
            ],
        ];

        foreach ($products as $productData) {
            Product::create([
                'tenant_id' => $tenant->id,
                ...$productData,
            ]);
        }

        $this->command->info('Products seeded successfully!');
    }
}

