<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('ads:expire')->dailyAt('00:00');
Schedule::command('ads:expire-featured')->dailyAt('00:00');
Schedule::command('subscriptions:expire')->dailyAt('00:00');
Schedule::command('subscriptions:notify-expiring')->dailyAt('09:00');
