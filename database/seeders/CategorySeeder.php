<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $categories = [
            // Expenses
            ['name' => 'Groceries & Food', 'type' => 'expense'],
            ['name' => 'Electricity & Gas Bills', 'type' => 'expense'],
            ['name' => 'Rent & Housing', 'type' => 'expense'],
            ['name' => 'Fuel & Transport', 'type' => 'expense'],
            ['name' => 'Dining Out & Cafes', 'type' => 'expense'],
            ['name' => 'Internet & Mobile Load', 'type' => 'expense'],
            ['name' => 'Healthcare & Medicines', 'type' => 'expense'],
            ['name' => 'Online Shopping', 'type' => 'expense'],
            // Income
            ['name' => 'Monthly Salary', 'type' => 'income'],
            ['name' => 'Freelance Projects', 'type' => 'income'],
            ['name' => 'Investments & Profit', 'type' => 'income'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate([
                'user_id' => $user->id,
                'name' => $cat['name'],
                'type' => $cat['type'],
            ]);
        }
    }
}