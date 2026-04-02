<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
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

        $expenseCategories = Category::leaves()
            ->whereHas('parent', fn ($q) => $q->where('name', '!=', 'Income'))
            ->get();

        $incomeCategories = Category::leaves()
            ->whereHas('parent', fn ($q) => $q->where('name', 'Income'))
            ->get();

        // ~100 expenses spread over the last 6 months
        Transaction::factory()
            ->count(100)
            ->expense()
            ->sequence(fn () => [
                'category_id' => $expenseCategories->random()->id,
                'date' => fake()->dateTimeBetween('-6 months'),
                'amount' => fake()->randomFloat(2, 1, 300),
            ])
            ->create();

        // ~20 incomes (mostly salary, some secondary)
        Transaction::factory()
            ->count(20)
            ->income()
            ->sequence(fn () => [
                'category_id' => $incomeCategories->random()->id,
                'date' => fake()->dateTimeBetween('-6 months'),
                'amount' => fake()->randomFloat(2, 50, 2500),
            ])
            ->create();
    }
}
