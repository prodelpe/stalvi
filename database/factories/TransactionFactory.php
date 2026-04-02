<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'category_id' => Category::factory(),
            'type' => fake()->randomElement(TransactionType::cases()),
            'amount' => fake()->randomFloat(2, 1, 500),
            'description' => fake()->optional()->sentence(),
            'date' => fake()->dateTimeBetween('-1 year'),
        ];
    }

    public function expense(): static
    {
        return $this->state(['type' => TransactionType::Expense]);
    }

    public function income(): static
    {
        return $this->state(['type' => TransactionType::Income]);
    }
}
