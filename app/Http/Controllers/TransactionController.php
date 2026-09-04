<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;


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
        if($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if($request->filled('to_date')) {
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
        $validated = $request->validated();
        $userId = auth()->id();

        DB::transaction(function () use ($validated, $userId) {
            $account = Account::where('id', $validated['account_id'])
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->firstOrFail();

            if($validated['type'] === 'income') {
                $account->increment('current_balance', $validated['amount']);
            } elseif($validated['type'] === 'expense') {
                $account->decrement('current_balance', $validated['amount']);
            }elseif($validated['type'] === 'transfer') {
                $toAccount = Account::where('id', $validated['to_account_id'])
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

                $account->decrement('current_balance', $validated['amount']);
                $toAccount->increment('current_balance', $validated['amount']);
            }

            Transaction::create(array_merge($validated, ['user_id' => $userId]));
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction saved successfully.');
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

        DB::transaction(function () use ($transaction){
            $account = Account::where('id', $transaction->account_id)
            ->lockForUpdate()
            ->first();

            if($transaction->type === 'income') {
                $account?->decrement('current_balance', $transaction->amount);
            } elseif($transaction->type === 'expense') {
                $account?->increment('current_balance', $transaction->amount);
            } elseif($transaction->type === 'transfer') {
                $toAccount = Account::where('id', $transaction->to_account_id)
                ->lockForUpdate()
                ->first();

                $account?->increment('current_balance', $transaction->amount);
                $toAccount?->decrement('current_balance', $transaction->amount);
            }

            $transaction->delete();
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction revert and deleted.');
    }
}
