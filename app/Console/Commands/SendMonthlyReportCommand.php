<?php

namespace App\Console\Commands;

use App\Mail\MonthlyReportMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendMonthlyReportCommand extends Command
{
    protected $signature = 'reports:send-monthly';
    protected $description = 'Send monthly financial reports to all users on the 1st of every month';

    public function handle()
    {
        $lastMonth = Carbon::now()->subMonth();
        $monthName = $lastMonth->format('F Y');
        $year = $lastMonth->year;
        $month = $lastMonth->month;

        $users = User::all();

        foreach ($users as $user) {
            $income = (float) $user->transactions()
                ->where('type', 'income')
                ->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->sum('amount');

            $expense = (float) $user->transactions()
                ->where('type', 'expense')
                ->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->sum('amount');

            $topCategories = $user->transactions()
                ->select('categories.name as category_name', DB::raw('SUM(transactions.amount) as total'))
                ->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.type', 'expense')
                ->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total')
                ->take(5)
                ->get();

            Mail::to($user->email)->send(new MonthlyReportMail(
                $user,
                $monthName,
                $income,
                $expense,
                $income - $expense,
                $topCategories
            ));

            // In-app notification bhi send karein
            $user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\Notifications\MonthlyReportGenerated',
                'data' => [
                    'title' => '📊 Monthly Report Generated',
                    'message' => "Your financial report for {$monthName} has been emailed and is available.",
                    'type' => 'info',
                    'url' => route('reports.index'),
                ],
            ]);
        }

        $this->info('Monthly reports successfully dispatched!');
    }
}