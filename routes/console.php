<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('payouts:process')->daily();
Schedule::command('app:check-expiring-inventory')->daily();
Schedule::command('app:auto-confirm-receipts')->everyMinute();
Schedule::command('app:monitor-pending-orders')->everyMinute();
