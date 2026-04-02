<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Allocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Allocation>
 */
class AllocationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_account_id' => Account::factory(),
            'destination_account_id' => Account::factory(),
            'amount' => fake()->randomFloat(2, 50, 1000),
            'is_active' => true,
        ];
    }
}
