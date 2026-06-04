<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /** @var array<string> */
    private array $fixedCategories = [
        'Electricity', 'Gas', 'Community', 'Internet', 'Credit',
        'Gym', 'Subscriptions', 'To savings', 'From savings',
    ];

    public function run(): void
    {
        $categories = [
            ['name' => 'Home', 'color' => '#ef4444', 'children' => ['Electricity', 'Gas', 'Community', 'Internet', 'Credit']],
            ['name' => 'Food', 'color' => '#f97316', 'children' => ['Supermarket', 'Restaurant', 'Coffee']],
            ['name' => 'Transport', 'color' => '#3b82f6', 'children' => ['Fuel', 'Public transport', 'Parking']],
            ['name' => 'Leisure', 'color' => '#a855f7', 'children' => ['Cinema', 'Music', 'Travel', 'Subscriptions']],
            ['name' => 'Health', 'color' => '#22c55e', 'children' => ['Pharmacy', 'Gym']],
            ['name' => 'Other', 'color' => '#6b7280', 'children' => ['Clothing', 'Gifts']],
            ['name' => 'Income', 'color' => '#10b981', 'children' => ['Salary', 'Wallapop', 'Vinted']],
            ['name' => 'Transfers', 'color' => '#8b5cf6', 'children' => ['To savings', 'From savings']],
        ];

        foreach ($categories as $data) {
            $parent = Category::create([
                'name' => $data['name'],
                'color' => $data['color'],
            ]);

            foreach ($data['children'] as $childName) {
                Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'is_fixed' => in_array($childName, $this->fixedCategories),
                ]);
            }
        }
    }
}
