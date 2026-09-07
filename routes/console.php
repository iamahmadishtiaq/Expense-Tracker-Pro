<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\ProcessRecurringTransactions;

Artisan::command('transactions:process-recurring', function () {
    $this->call(ProcessRecurringTransactions::class);
})->purpose('Process due recurring transactions');

Schedule::command('transactions:process-recurring')->dailyAt('00:01');