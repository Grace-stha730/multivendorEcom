<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('ratings:recalculate')->hourly();
Schedule::command('recommendations:rebuild-vectors')->daily();
Schedule::command('ai:reply-to-pending-support')->everyFiveMinutes();
Schedule::command('ai:generate-recommendation-blurbs')->daily();