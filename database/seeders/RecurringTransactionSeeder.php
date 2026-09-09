<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RecurringTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $bank = Account::where('user_id', $user->id)->where('type', 'bank')->first();
        $salaryCat = Category::where('user_id', $user->id)->where('name', 'Monthly Salary')->first();
        $billsCat = Category::where('user_id', $user->id)->where('name', 'Internet & Mobile Load')->first();

        if ($bank && $salaryCat) {
            RecurringTransaction::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'description' => 'Office Monthly Salary',
                ],
                [
                    'account_id' => $bank->id,
                    'category_id' => $salaryCat->id,
                    'type' => 'income',
                    'amount' => 220000,
                    'frequency' => 'monthly',
                    'start_date' => Carbon::now()->startOfMonth(),
                    'next_run_date' => Carbon::now()->addMonth()->startOfMonth(),
                    'is_active' => true,
                ]
            );
        }

        if ($bank && $billsCat) {
            RecurringTransaction::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'description' => 'Fiber Internet Bill (StormFiber)',
                ],
                [
                    'account_id' => $bank->id,
                    'category_id' => $billsCat->id,
                    'type' => 'expense',
                    'amount' => 4500,
                    'frequency' => 'monthly',
                    'start_date' => Carbon::now()->startOfMonth()->addDays(9),
                    'next_run_date' => Carbon::now()->addMonth()->startOfMonth()->addDays(9),
                    'is_active' => true,
                ]
            );
        }
    }
}