<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('billing:run')->daily();
Schedule::command('expenses:run')->dailyAt('01:30');
Schedule::command('platform:bill')->dailyAt('02:00');
Schedule::command('sms:dispatch-scheduled')->everyMinute();
