<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $budgetAllocations = [
            'Groceries & Food' => 45000,
            'Fuel & Transport' => 20000,
            'Dining Out & Cafes' => 15000,
            'Electricity & Gas Bills' => 30000,
            'Internet & Mobile Load' => 8000,
        ];

        $currentMonthInt = (int) now()->format('m'); // 9
        $currentYearInt  = (int) now()->format('Y'); // 2026
        $currentMonthStr = now()->format('Y-m');     // "2026-09"

        $hasYearColumn = Schema::hasColumn('budgets', 'year');

        foreach ($budgetAllocations as $categoryName => $amount) {
            $category = Category::where('user_id', $user->id)
                ->where('name', $categoryName)
                ->where('type', 'expense')
                ->first();

            if ($category) {
                if ($hasYearColumn) {
                    Budget::updateOrCreate(
                        [
                            'user_id'     => $user->id,
                            'category_id' => $category->id,
                            'month'       => $currentMonthInt,
                            'year'        => $currentYearInt,
                        ],
                        ['amount' => $amount]
                    );
                } else {
                    // Agar column integer hai aur sirf month number leta hai (1-12)
                    Budget::updateOrCreate(
                        [
                            'user_id'     => $user->id,
                            'category_id' => $category->id,
                            'month'       => $currentMonthInt,
                        ],
                        ['amount' => $amount]
                    );
                }
            }
        }
    }
}