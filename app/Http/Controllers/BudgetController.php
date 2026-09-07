<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $budgets = $user->budgets()
            ->with('category')
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->map(function ($budget) use ($user, $month, $year) {
                $spent = (float) $user->transactions()
                    ->where('type', 'expense')
                    ->where('category_id', $budget->category_id)
                    ->whereYear('transaction_date', $year)
                    ->whereMonth('transaction_date', $month)
                    ->sum('amount');

                $budget->spent = $spent;
                $budget->remaining = (float) $budget->amount - $spent;
                $budget->percentage = $budget->amount > 0 ? min(round(($spent / $budget->amount) * 100), 100) : 0;
                $budget->is_exceeded = $spent > $budget->amount;
                $budget->is_warning = $spent >= ($budget->amount * 0.8) && !$budget->is_exceeded;

                return $budget;
            });

        $categories = $user->categories()->where('type', 'expense')->orderBy('name')->get();
        return view('budgets.index', compact('budgets', 'categories', 'month', 'year'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userId = auth()->id();

        $validated = $request->validate([
            'category_id' => [
                'required',
                'exists:categories,id',
                Rule::unique('budgets')->where(
                    fn($query) =>
                    $query->where('user_id', $userId)
                        ->where('month', $request->month)
                        ->where('year', $request->year)
                ),
            ],
            'amount' => ['required', 'numeric', 'min:1'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2024', 'max:2035'],
            'category_id.unique' => 'A budget for this category and month already exists',
        ]);

        $request->user()->budgets()->create($validated);

        return redirect()->route('budgets.index', [
            'month' => $request->month,
            'year' => $request->year,

        ])->with('success', 'Budget set successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Budget $budget)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget)
    {
        abort_if($budget->user_id !== auth()->id(), 403);

        $month = $budget->month;
        $year = $budget->year;
        $budget->delete();

        return redirect()->route('budgets.index', compact('month', 'year'))
            ->with('success', 'Budget removed successfully');
    }
}
