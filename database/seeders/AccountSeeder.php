<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Allocation;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Seed the application's database with default accounts and allocations.
     */
    public function run(): void
    {
        $current = Account::create([
            'name' => 'Current',
            'icon' => "\u{1F4B3}",
            'color' => '#3b82f6',
            'initial_balance' => 0,
        ]);

        $savings = Account::create([
            'name' => 'Savings',
            'icon' => "\u{1F3E6}",
            'color' => '#22c55e',
            'initial_balance' => 0,
        ]);

        Allocation::create([
            'source_account_id' => $current->id,
            'destination_account_id' => $savings->id,
            'amount' => 500.00,
            'is_active' => true,
        ]);
    }
}
