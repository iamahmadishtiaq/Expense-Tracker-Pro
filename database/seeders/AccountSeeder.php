<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $accounts = [
            ['name' => 'Meezan Bank (Salary)', 'type' => 'bank', 'opening_balance' => 150000, 'current_balance' => 150000],
            ['name' => 'HBL Savings', 'type' => 'bank', 'opening_balance' => 80000, 'current_balance' => 80000],
            ['name' => 'Cash in Hand / Wallet', 'type' => 'cash', 'opening_balance' => 25000, 'current_balance' => 25000],
            ['name' => 'JazzCash', 'type' => 'wallet', 'opening_balance' => 12000, 'current_balance' => 12000],
            ['name' => 'Sadapay / Nayapay', 'type' => 'wallet', 'opening_balance' => 18000, 'current_balance' => 18000],
        ];

        foreach ($accounts as $acc) {
            Account::firstOrCreate(
                ['user_id' => $user->id, 'name' => $acc['name']],
                [
                    'type' => $acc['type'],
                    'opening_balance' => $acc['opening_balance'],
                    'current_balance' => $acc['current_balance'],
                ]
            );
        }
    }
}