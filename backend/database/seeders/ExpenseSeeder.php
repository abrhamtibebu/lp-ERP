<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        $users = User::where('tenant_id', $tenant->id)->get();

        if ($users->isEmpty()) {
            $this->command->warn('Users not found. Please run UserSeeder first.');
            return;
        }

        $costCenters = ['Production', 'Administration', 'Sales', 'Logistics', 'Maintenance'];
        $categories = ['Raw Materials', 'Utilities', 'Salaries', 'Transportation', 'Office Supplies', 'Maintenance', 'Marketing'];

        $expenses = [];
        
        $expenseDescriptions = [
            'Raw materials purchase', 'Electricity bill', 'Water bill', 'Employee salaries',
            'Transportation costs', 'Office supplies', 'Machine maintenance', 'Marketing campaign',
            'Rent payment', 'Insurance premium', 'Phone and internet', 'Cleaning services',
            'Security services', 'Equipment repair', 'Training costs', 'Software license',
            'Fuel costs', 'Packaging materials', 'Quality testing', 'Waste disposal',
            'Legal fees', 'Accounting services', 'Bank charges', 'Tax payments',
            'Employee benefits', 'Medical expenses', 'Travel expenses', 'Conference fees',
            'Equipment upgrade', 'Building maintenance',
        ];

        // Generate expenses for the last 3 months
        for ($i = 0; $i < 30; $i++) {
            $expenses[] = [
                'tenant_id' => $tenant->id,
                'description' => $expenseDescriptions[array_rand($expenseDescriptions)],
                'amount' => round(rand(50000, 5000000) / 100, 2), // Random between 500 and 50000
                'cost_center' => $costCenters[array_rand($costCenters)],
                'category' => $categories[array_rand($categories)],
                'expense_date' => now()->subDays(rand(0, 90)),
                'created_by' => $users->random()->id,
            ];
        }

        foreach ($expenses as $expenseData) {
            Expense::create($expenseData);
        }

        $this->command->info('Expenses seeded successfully!');
    }
}

