<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Log;

final class Kernel extends ConsoleKernel
{
    /**
     * @var array<int, class-string<\Illuminate\Console\Command>>
     */
    protected $commands = [
        \App\Console\Commands\BackupPrepareCommand::class,
        \App\Console\Commands\BackupVerifyCommand::class,
        \App\Console\Commands\FixCodeStyleCommand::class,
        \App\Console\Commands\ValidateCodeStyleCommand::class,
        \App\Console\Commands\CodeStyleWatchCommand::class,
        \App\Console\Commands\DemonstrateTimeoutCommand::class,
        \App\Console\Commands\GenerateApiSpecCommand::class,
        \App\Console\Commands\GenerateReportsCommand::class,
        \App\Console\Commands\CheckRefreshDatabaseCommand::class,
        \App\Console\Commands\DbAuditIndexesCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Run code style validation daily at 2 AM
        $schedule
            ->command('code-style:validate --strict')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->onFailure(function (): void {
                \Log::error('Daily code style validation failed');
            });

        // Run code style fix weekly on Sundays at 3 AM
        $schedule
            ->command('code-style:fix --path=app --report')
            ->weeklyOn(0, '03:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->onSuccess(function (): void {
                \Log::info('Weekly code style fix completed successfully');
            });

        $prepareSchedule = config('backup.schedule.prepare');
        if (is_string($prepareSchedule) && $prepareSchedule !== '') {
            $schedule
                ->command('backup:prepare')
                ->cron($prepareSchedule)
                ->withoutOverlapping()
                ->runInBackground();
        }

        $verifySchedule = config('backup.schedule.verify');
        if (is_string($verifySchedule) && $verifySchedule !== '') {
            $schedule
                ->command('backup:verify')
                ->cron($verifySchedule)
                ->withoutOverlapping()
                ->runInBackground();
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
