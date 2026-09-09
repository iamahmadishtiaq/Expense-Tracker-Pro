<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\ProcessRecurringTransactions;
use App\Console\Commands\SendMonthlyReportCommand;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automated Tasks Schedule
Schedule::command(ProcessRecurringTransactions::class)->dailyAt('00:01');
Schedule::command(SendMonthlyReportCommand::class)->monthlyOn(1, '09:00');