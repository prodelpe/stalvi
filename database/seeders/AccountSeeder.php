<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Seed the application's database with default accounts.
     */
    public function run(): void
    {
        Account::create([
            'name' => 'Current',
            'icon' => "\u{1F4B3}",
            'color' => '#3b82f6',
            'initial_balance' => 0,
        ]);

        Account::create([
            'name' => 'Savings',
            'icon' => "\u{1F3E6}",
            'color' => '#22c55e',
            'initial_balance' => 0,
        ]);
    }
}
