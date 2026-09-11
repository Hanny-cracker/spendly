<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(
    'transactions:generate-recurring'
)
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('transactions:notify-upcoming')
    ->everyMinute()
    ->withoutOverlapping();
