<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

final class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\FixCodeStyleCommand::class,
        \App\Console\Commands\ValidateCodeStyleCommand::class,
        \App\Console\Commands\CodeStyleWatchCommand::class,
        \App\Console\Commands\DemonstrateTimeoutCommand::class,
        \App\Console\Commands\GenerateReportsCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Run code style validation daily at 2 AM
        $schedule
            ->command('code-style:validate --strict')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->onFailure(function () {
                \Log::error('Daily code style validation failed');
            });

        // Run code style fix weekly on Sundays at 3 AM
        $schedule
            ->command('code-style:fix --path=app --report')
            ->weeklyOn(0, '03:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->onSuccess(function () {
                \Log::info('Weekly code style fix completed successfully');
            });
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
