<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Models\RecurringTransaction;

#[Signature('app:process-recurring-transactions')]
#[Description('Command description')]
class ProcessRecurringTransactions extends Command
{

    protected $signature = 'transactions:process-recurring';
    protected $description = 'Process due recurring transaction and update balances';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();
        $dueSubscriptions = RecurringTransaction::with('account')
            ->where('is_active', true)
            ->whereDate('next_run_date', '<=', $today)
            ->get();

        if ($dueSubscriptions->isEmpty()) {
            $this->info('No recurring transactions to process');
            return self::SUCCESS;
        }

        foreach ($dueSubscriptions as $sub) {
            DB::transaction(function () use ($sub, $today) {

                Transaction::create([
                    'user_id' => $sub->user_id,
                    'account_id' => $sub->account_id,
                    'category_id' => $sub->category_id,
                    'type' => $sub->type,
                    'amount' => $sub->amount,
                    'transaction_date' => $sub->next_run_date,
                    'description' => ($sub->description ? $sub->description . ' ' : '') . '(Auto-Recurring)',
                ]);

                $account = $sub->account;
                if ($sub->type === 'income') {
                    $account->increment('current_balance', $sub->amount);
                } else {
                    $account->decrement('current_balance', $sub->amount);
                }

                $sub->update([
                    'next_run_date' => $sub->calculateNextRunDate(Carbon::parse($sub->next_run_date)),
                ]);
            });

            $this->line('Processed: {$sub->description} [PKR {$sub->amount}]');
        }

        $this->info("Successfully processed {$dueSubscriptions->count()} recurring transactions.");
        return self::SUCCESS;
    }
}
