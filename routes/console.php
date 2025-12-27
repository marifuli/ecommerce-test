<?php

use App\Console\Commands\SendDailySalesReport;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily sales report to run every day at 9:00 AM
Schedule::command('sales:daily-report')
    ->dailyAt('09:00')
    ->timezone('UTC')
    ->description('Send daily sales report to admin');
