<?php

declare(strict_types=1);

use App\Jobs\CheckLowStockJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule low stock checks every 6 hours
Schedule::job(new CheckLowStockJob)->everySixHours();

// Schedule cache warmup every hour
Schedule::call(function () {
    \App\Services\CacheService::warmupCaches();
})->hourly();

// Clear old activity logs (keep 90 days) with timeout protection
Schedule::call(function () {
    $timeout = now()->addMinutes(5); // 5 minute timeout for log cleanup
    
    \Spatie\Activitylog\Models\Activity::where('created_at', '<', now()->subDays(90))
        ->cursor()
        ->takeUntilTimeout($timeout)
        ->each(function ($activity) {
            $activity->delete();
        });
})->daily();
