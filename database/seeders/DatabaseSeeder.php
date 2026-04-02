<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->isLocal()) {
            return;
        }

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->seedCategories();

        $this->call(AccountSeeder::class);
        $this->call(DevelopmentSeeder::class);
    }

    private function seedCategories(): void
    {
        $categories = [
            ['icon' => "\u{1F3E0}", 'name' => 'Home', 'color' => '#ef4444', 'children' => ['Electricity', 'Gas', 'Community', 'Internet', 'Mortgage']],
            ['icon' => "\u{1F37D}\u{FE0F}", 'name' => 'Food', 'color' => '#f97316', 'children' => ['Supermarket', 'Restaurant', 'Coffee']],
            ['icon' => "\u{1F697}", 'name' => 'Transport', 'color' => '#3b82f6', 'children' => ['Fuel', 'Public transport', 'Parking']],
            ['icon' => "\u{1F389}", 'name' => 'Leisure', 'color' => '#a855f7', 'children' => ['Cinema', 'Music', 'Travel', 'Subscriptions']],
            ['icon' => "\u{1F48A}", 'name' => 'Health', 'color' => '#22c55e', 'children' => ['Pharmacy', 'Gym']],
            ['icon' => "\u{1F4E6}", 'name' => 'Other', 'color' => '#6b7280', 'children' => ['Clothing', 'Gifts']],
            ['icon' => "\u{1F4B0}", 'name' => 'Income', 'color' => '#10b981', 'children' => ['Salary', 'Wallapop', 'Vinted']],
        ];

        foreach ($categories as $data) {
            $parent = Category::create([
                'name' => $data['name'],
                'icon' => $data['icon'],
                'color' => $data['color'],
            ]);

            foreach ($data['children'] as $childName) {
                Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                ]);
            }
        }
    }
}
