<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Notifications\BudgetAlertNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = $user->transactions()
            ->with(['account', 'toAccount', 'category'])
            ->latest('transaction_date')
            ->latest('id');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->paginate(15)->withQueryString();
        $accounts = $user->accounts()->orderBy('name')->get();
        $categories = $user->categories()->orderBy('name')->get();
        
        return view('transactions.index', compact('transactions', 'accounts', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $accounts = $user->accounts()->orderBy('name')->get();
        $categories = $user->categories()->orderBy('name')->get();

        return view('transactions.create', compact('accounts', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        $user = auth()->user();
        $userId = $user->id;
        $validated = $request->validated();

        // Handle receipt image upload
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        // Nullify irrelevant foreign keys
        if ($validated['type'] === 'transfer') {
            $validated['category_id'] = null;
        } else {
            $validated['to_account_id'] = null;
        }

        // Database Execution
        DB::transaction(function () use ($validated, $userId, $receiptPath) {
            $sourceAccount = Account::where('id', $validated['account_id'])
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($validated['type'] === 'income') {
                $sourceAccount->increment('current_balance', $validated['amount']);
            } elseif ($validated['type'] === 'expense') {
                $sourceAccount->decrement('current_balance', $validated['amount']);
            } elseif ($validated['type'] === 'transfer') {
                $targetAccount = Account::where('id', $validated['to_account_id'])
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $sourceAccount->decrement('current_balance', $validated['amount']);
                $targetAccount->increment('current_balance', $validated['amount']);
            }

            Transaction::create(array_merge($validated, [
                'user_id' => $userId,
                'receipt_path' => $receiptPath,
            ]));
        });

        // Check & Trigger Budget Overrun Notification
        if ($validated['type'] === 'expense' && !empty($validated['category_id'])) {
            $now = now();
            $budget = Budget::with('category')
                ->where('user_id', $userId)
                ->where('category_id', $validated['category_id'])
                ->where('month', (int) $now->format('m'))
                ->first();

            if ($budget && $budget->amount > 0) {
                $totalSpent = (float) Transaction::where('user_id', $userId)
                    ->where('category_id', $budget->category_id)
                    ->where('type', 'expense')
                    ->whereMonth('transaction_date', $now->month)
                    ->whereYear('transaction_date', $now->year)
                    ->sum('amount');

                $budgetLimit = (float) $budget->amount;
                $percentage = round(($totalSpent / $budgetLimit) * 100, 1);

                if ($percentage >= 80) {
                    $categoryTitle = $budget->category?->name ?? 'Category';

                    // Prevent duplicate notifications on the same day for this category
                    $alreadyNotified = $user->unreadNotifications()
                        ->where('data->category', $categoryTitle)
                        ->whereDate('created_at', $now->toDateString())
                        ->exists();

                    if (!$alreadyNotified) {
                        $user->notify(new BudgetAlertNotification(
                            $categoryTitle,
                            $totalSpent,
                            $budgetLimit,
                            $percentage,
                            $percentage >= 100
                        ));
                    }
                }
            }
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction saved successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        abort_if($transaction->user_id !== auth()->id(), 403, 'Unauthorized action.');

        $receiptPath = $transaction->receipt_path;

        DB::transaction(function () use ($transaction) {
            $account = Account::where('id', $transaction->account_id)
                ->lockForUpdate()
                ->first();

            if ($transaction->type === 'income') {
                $account?->decrement('current_balance', $transaction->amount);
            } elseif ($transaction->type === 'expense') {
                $account?->increment('current_balance', $transaction->amount);
            } elseif ($transaction->type === 'transfer') {
                $toAccount = Account::where('id', $transaction->to_account_id)
                    ->lockForUpdate()
                    ->first();

                $account?->increment('current_balance', $transaction->amount);
                $toAccount?->decrement('current_balance', $transaction->amount);
            }

            $transaction->delete();
        });

        // Delete uploaded file if exists
        if ($receiptPath && Storage::disk('public')->exists($receiptPath)) {
            Storage::disk('public')->delete($receiptPath);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction reverted and deleted.');
    }

    /**
     * Export transactions as CSV stream.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = auth()->user();
        $query = $user->transactions()
            ->with(['account', 'toAccount', 'category'])
            ->latest('transaction_date')
            ->latest('id');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        $fileName = 'transactions_' . now()->format('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($handle, ['ID', 'Date', 'Type', 'Account', 'Transfer To', 'Category', 'Amount (PKR)', 'Description']);

            // Chunking prevents memory overflow
            $query->chunk(250, function ($transactions) use ($handle) {
                foreach ($transactions as $tx) {
                    fputcsv($handle, [
                        $tx->id,
                        $tx->transaction_date->format('Y-m-d'),
                        ucfirst($tx->type),
                        $tx->account?->name ?? 'N/A',
                        $tx->toAccount?->name ?? '-',
                        $tx->category?->name ?? '-',
                        $tx->amount,
                        $tx->description ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}