<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $accounts = Account::where('user_id', $user->id)->get();
        $categories = Category::where('user_id', $user->id)->get();

        if ($accounts->isEmpty() || $categories->isEmpty()) return;

        $salaryCat = $categories->firstWhere('name', 'Monthly Salary');
        $freelanceCat = $categories->firstWhere('name', 'Freelance Projects');
        $groceriesCat = $categories->firstWhere('name', 'Groceries & Food');
        $fuelCat = $categories->firstWhere('name', 'Fuel & Transport');
        $diningCat = $categories->firstWhere('name', 'Dining Out & Cafes');
        $billsCat = $categories->firstWhere('name', 'Electricity & Gas Bills');

        $bankAcc = $accounts->firstWhere('type', 'bank') ?? $accounts->first();
        $cashAcc = $accounts->firstWhere('type', 'cash') ?? $accounts->first();
        $walletAcc = $accounts->firstWhere('type', 'wallet') ?? $accounts->first();

        // Pichle 3 mahino ka realistic flow
        for ($monthOffset = 2; $monthOffset >= 0; $monthOffset--) {
            $baseDate = Carbon::now()->subMonths($monthOffset);

            // 1. Monthly Salary (1st of month)
            if ($salaryCat) {
                $this->createTx($user->id, $bankAcc->id, $salaryCat->id, 'income', 220000, $baseDate->copy()->startOfMonth(), 'Monthly Client Salary');
            }

            // 2. Freelance Payout (mid month)
            if ($freelanceCat) {
                $this->createTx($user->id, $walletAcc->id, $freelanceCat->id, 'income', 65000, $baseDate->copy()->startOfMonth()->addDays(14), 'Upwork Milestone Payment');
            }

            // 3. Rent & Utility Bills
            if ($billsCat) {
                $this->createTx($user->id, $bankAcc->id, $billsCat->id, 'expense', 28500, $baseDate->copy()->startOfMonth()->addDays(5), 'Electricity & Internet Bill');
            }

            // 4. Multiple Weekly Groceries
            if ($groceriesCat) {
                $this->createTx($user->id, $cashAcc->id, $groceriesCat->id, 'expense', 11500, $baseDate->copy()->startOfMonth()->addDays(3), 'Carrefour Monthly Rashan');
                $this->createTx($user->id, $walletAcc->id, $groceriesCat->id, 'expense', 7800, $baseDate->copy()->startOfMonth()->addDays(12), 'Meat & Fresh Vegetables');
                $this->createTx($user->id, $cashAcc->id, $groceriesCat->id, 'expense', 9200, $baseDate->copy()->startOfMonth()->addDays(20), 'Supermarket Household Stock');
            }

            // 5. Fuel
            if ($fuelCat) {
                $this->createTx($user->id, $cashAcc->id, $fuelCat->id, 'expense', 6500, $baseDate->copy()->startOfMonth()->addDays(7), 'PSO Petrol Fillup');
                $this->createTx($user->id, $cashAcc->id, $fuelCat->id, 'expense', 7000, $baseDate->copy()->startOfMonth()->addDays(21), 'Total Parco Fuel Refill');
            }

            // 6. Dining Out
            if ($diningCat) {
                $this->createTx($user->id, $walletAcc->id, $diningCat->id, 'expense', 4500, $baseDate->copy()->startOfMonth()->addDays(10), 'Weekend Family Dinner');
                $this->createTx($user->id, $cashAcc->id, $diningCat->id, 'expense', 2800, $baseDate->copy()->startOfMonth()->addDays(18), 'Coffee & Cafe with Friends');
            }
        }
    }

    private function createTx($userId, $accId, $catId, $type, $amount, $date, $desc): void
    {
        Transaction::create([
            'user_id' => $userId,
            'account_id' => $accId,
            'category_id' => $catId,
            'type' => $type,
            'amount' => $amount,
            'transaction_date' => $date->format('Y-m-d'),
            'description' => $desc,
        ]);

        $acc = Account::find($accId);
        if ($acc) {
            if ($type === 'income') {
                $acc->increment('current_balance', $amount);
            } else {
                $acc->decrement('current_balance', $amount);
            }
        }
    }
}