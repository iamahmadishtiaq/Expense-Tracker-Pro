<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Default: Start of current month to today
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $query = $user->transactions()
            ->with(['account', 'toAccount', 'category'])
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        // Optional Account filter
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Optional Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = (clone $query)->latest('transaction_date')->latest('id')->paginate(15)->withQueryString();

        // Financial Summary for the filtered range
        $totalIncome = (float) (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (float) (clone $query)->where('type', 'expense')->sum('amount');
        $netSavings = $totalIncome - $totalExpense;

        $accounts = $user->accounts()->orderBy('name')->get();

        return view('reports.index', compact(
            'transactions',
            'totalIncome',
            'totalExpense',
            'netSavings',
            'startDate',
            'endDate',
            'accounts'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $user = auth()->user();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $query = $user->transactions()
            ->with(['account', 'toAccount', 'category'])
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $fileName = 'financial-report-' . $startDate . '-to-' . $endDate . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // CSV Header Row
            fputcsv($handle, ['ID', 'Date', 'Type', 'Account', 'Destination Account', 'Category', 'Amount (PKR)', 'Description']);

            $query->chunk(200, function ($transactions) use ($handle) {
                foreach ($transactions as $tx) {
                    fputcsv($handle, [
                        $tx->id,
                        $tx->transaction_date,
                        ucfirst($tx->type),
                        $tx->account->name,
                        $tx->toAccount?->name ?? 'N/A',
                        $tx->category?->name ?? 'N/A',
                        $tx->amount,
                        $tx->description ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }
}