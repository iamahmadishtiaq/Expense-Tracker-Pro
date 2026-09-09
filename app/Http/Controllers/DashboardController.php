<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\Budget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        // ---------------- Budget Overrun Alerts ---------------- //
        $budgetQuery = $user->budgets()->with('category')->where('month', $currentMonth);
        
        if (Schema::hasColumn('budgets', 'year')) {
            $budgetQuery->where('year', $currentYear);
        }

        $budgets = $budgetQuery->get();
        $budgetAlerts = [];

        // Category-wise totals ko key-value map bana kar memory mein fast match karna
        $spentMap = $user->transactions()
            ->where('type', 'expense')
            ->whereNotNull('category_id')
            ->whereYear('transaction_date', $currentYear)
            ->whereMonth('transaction_date', $currentMonth)
            ->groupBy('category_id')
            ->select('category_id', DB::raw('SUM(amount) as total_spent'))
            ->pluck('total_spent', 'category_id');

        foreach ($budgets as $budget) {
            $spent = (float) ($spentMap[$budget->category_id] ?? 0);
            $limit = (float) $budget->amount;
            $percentage = $limit > 0 ? round(($spent / $limit) * 100, 1) : 0;

            if ($percentage >= 80) {
                $budgetAlerts[] = [
                    'category'    => $budget->category?->name ?? 'Category',
                    'budget'      => $limit,
                    'spent'       => $spent,
                    'percentage'  => $percentage,
                    'is_exceeded' => $percentage >= 100,
                ];
            }
        }

        return view('dashboard', compact(
            'totalBalance',
            'monthlyIncome',
            'monthlyExpense',
            'monthlySavings',
            'expensesByCategory',
            'recentTransactions',
            'budgetAlerts'
        ));
    }
}