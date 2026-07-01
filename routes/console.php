<?php

use App\Console\Commands\SendReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SendReminders::class)
    ->dailyAt('08:00')
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping()
    ->onOneServer();
