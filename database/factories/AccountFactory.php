<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'icon' => fake()->emoji(),
            'color' => fake()->hexColor(),
            'initial_balance' => fake()->randomFloat(2, 0, 5000),
        ];
    }
}
