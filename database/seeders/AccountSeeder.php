<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Allocation;
use App\Models\Budget;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Seed the application's database with default accounts, allocations and budget.
     */
    public function run(): void
    {
        $current = Account::create([
            'name' => 'Current',
            'color' => '#3b82f6',
            'initial_balance' => 1500,
        ]);

        $savings = Account::create([
            'name' => 'Savings',
            'color' => '#22c55e',
            'initial_balance' => 700,
        ]);

        Allocation::create([
            'source_account_id' => $current->id,
            'destination_account_id' => $savings->id,
            'amount' => 500.00,
            'is_active' => true,
        ]);

        Budget::create([
            'monthly_income' => 1500.00,
        ]);
    }
}
