<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecurringTransactionController extends Controller
{
    public function index()
    {
        $recurringTransactions = auth()->user()->recurringTransactions()
            ->with(['account', 'category'])
            ->latest()
            ->paginate(10);

        return view('recurring.index', compact('recurringTransactions'));
    }

    public function create()
    {
        $user = auth()->user();
        $accounts = $user->accounts()->orderBy('name')->get();
        $categories = $user->categories()->orderBy('name')->get();

        return view('recurring.create', compact('accounts', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        auth()->user()->recurringTransactions()->create([
            'account_id' => $validated['account_id'],
            'category_id' => $validated['category_id'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'frequency' => $validated['frequency'],
            'start_date' => $validated['start_date'],
            'next_run_date' => $validated['start_date'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('recurring.index')->with('success', 'Recurring schedule created.');
    }

    public function toggle(RecurringTransaction $recurring)
    {
        abort_if($recurring->user_id !== auth()->id(), 403);

        $recurring->update([
            'is_active' => ! $recurring->is_active,
        ]);

        $status = $recurring->is_active ? 'resumed' : 'paused';
        return back()->with('success', "Recurring transaction {$status}.");
    }

    public function destroy(RecurringTransaction $recurring)
    {
        abort_if($recurring->user_id !== auth()->id(), 403);

        $recurring->delete();

        return redirect()->route('recurring.index')->with('success', 'Schedule deleted.');
    }
}