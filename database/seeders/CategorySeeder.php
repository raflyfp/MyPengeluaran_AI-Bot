<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed reusable default financial categories.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Salary', 'icon' => 'briefcase', 'type' => 'income'],
            ['name' => 'Freelance', 'icon' => 'laptop', 'type' => 'income'],
            ['name' => 'Cashback', 'icon' => 'sparkles', 'type' => 'income'],
            ['name' => 'Food & Drink', 'icon' => 'utensils', 'type' => 'expense'],
            ['name' => 'Groceries', 'icon' => 'shopping-cart', 'type' => 'expense'],
            ['name' => 'Transport', 'icon' => 'car', 'type' => 'expense'],
            ['name' => 'Bills & Utilities', 'icon' => 'receipt', 'type' => 'expense'],
            ['name' => 'Health', 'icon' => 'heart-pulse', 'type' => 'expense'],
            ['name' => 'Shopping', 'icon' => 'shopping-bag', 'type' => 'expense'],
            ['name' => 'Subscription', 'icon' => 'play-square', 'type' => 'expense'],
            ['name' => 'Savings Transfer', 'icon' => 'arrow-left-right', 'type' => 'expense'],
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate([
                'name' => $category['name'],
                'type' => $category['type'],
            ], [
                'icon' => $category['icon'],
            ]);
        }
    }
}
