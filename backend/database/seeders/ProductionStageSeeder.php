<?php

namespace Database\Seeders;

use App\Models\ProductionStage;
use Illuminate\Database\Seeder;

class ProductionStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Cutting', 'order' => 1],
            ['name' => 'Schiving', 'order' => 2],
            ['name' => 'Initial Stitching', 'order' => 3],
            ['name' => 'Final Assembly', 'order' => 4],
            ['name' => 'Binding', 'order' => 5],
            ['name' => 'Polishing & Painting', 'order' => 6],
            ['name' => 'Quality Inspection', 'order' => 7],
            ['name' => 'Goods at Inventory', 'order' => 8],
            ['name' => 'WIP', 'order' => 9],
            ['name' => 'Rework', 'order' => 10],
        ];

        foreach ($stages as $stage) {
            ProductionStage::create($stage);
        }
    }
}

