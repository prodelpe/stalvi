<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed fake transactions for local development.
     */
    public function run(): void
    {
        if (! app()->isLocal()) {
            return;
        }

        $currentAccount = Account::where('name', 'Current')->first();
        $savingsAccount = Account::where('name', 'Savings')->first();

        $expenseCategories = Category::leaves()
            ->whereHas('parent', fn ($q) => $q->where('name', '!=', 'Income'))
            ->get();

        $incomeCategories = Category::leaves()
            ->whereHas('parent', fn ($q) => $q->where('name', 'Income'))
            ->get();

        // ~100 expenses from the current account
        Transaction::factory()
            ->count(100)
            ->expense()
            ->sequence(fn () => [
                'account_id' => $currentAccount->id,
                'category_id' => $expenseCategories->random()->id,
                'date' => fake()->dateTimeBetween('-6 months'),
                'amount' => fake()->randomFloat(2, 1, 300),
            ])
            ->create();

        // ~18 incomes to current account
        Transaction::factory()
            ->count(18)
            ->income()
            ->sequence(fn () => [
                'account_id' => $currentAccount->id,
                'category_id' => $incomeCategories->random()->id,
                'date' => fake()->dateTimeBetween('-6 months'),
                'amount' => fake()->randomFloat(2, 50, 2500),
            ])
            ->create();

        // ~2 incomes to savings account
        Transaction::factory()
            ->count(2)
            ->income()
            ->sequence(fn () => [
                'account_id' => $savingsAccount->id,
                'category_id' => $incomeCategories->random()->id,
                'date' => fake()->dateTimeBetween('-6 months'),
                'amount' => fake()->randomFloat(2, 100, 1000),
            ])
            ->create();
    }
}
