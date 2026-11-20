<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled tracking sync for active shipments every 30 minutes
Schedule::command('shiprocket:sync-tracking')->everyThirtyMinutes()->withoutOverlapping();
