<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        $totalBalance = (float) $user->accounts()->sum('current_balance');

        $monthlyIncome = (float) $user->transactions()
            ->where('type', 'income')
            ->whereYear('transaction_date', $currentYear)
            ->whereMonth('transaction_date', $currentMonth)
            ->sum('amount');

        $monthlyExpense = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereYear('transaction_date', $currentYear)
            ->whereMonth('transaction_date', $currentMonth)
            ->sum('amount');

        $monthlySavings = $monthlyIncome - $monthlyExpense;

        $expensesByCategory = Transaction::select(
                'categories.name as category_name',
                DB::raw('SUM(transactions.amount) as total_amount')
            )
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereNotNull('transactions.category_id')
            ->whereYear('transactions.transaction_date', $currentYear)
            ->whereMonth('transactions.transaction_date', $currentMonth)
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_amount')
            ->get();

        $recentTransactions = $user->transactions()
            ->with(['account', 'toAccount', 'category'])
            ->latest('transaction_date')
            ->latest('id')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalBalance',
            'monthlyIncome',
            'monthlyExpense',
            'monthlySavings',
            'expensesByCategory',
            'recentTransactions'
        ));
    }
}