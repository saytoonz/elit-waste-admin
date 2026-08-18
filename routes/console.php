<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// withoutOverlapping() prevents a slow/overlapping run from double-billing.
// Add ->onOneServer() as well if this app is ever deployed across multiple servers.
Schedule::command('billing:run')->daily()->withoutOverlapping();
// Daily customer payment reminders (unpaid subscription bills, new invoices only).
Schedule::command('billing:remind')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('expenses:run')->dailyAt('01:30');
Schedule::command('platform:bill')->dailyAt('02:00');
Schedule::command('platform:remind')->dailyAt('08:00');
Schedule::command('sms:dispatch-scheduled')->everyMinute();
