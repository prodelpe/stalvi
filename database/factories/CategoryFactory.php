<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'color' => fake()->hexColor(),
        ];
    }

    public function child(Category $parent): static
    {
        return $this->state([
            'parent_id' => $parent->id,
            'color' => null,
        ]);
    }
}
