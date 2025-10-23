<?php

declare(strict_types=1);

namespace App\Console;

use App\Support\Logging\LogContext;
use App\Support\Logging\OperationLog;
use App\Support\Logging\StructuredLogger;
use Illuminate\Console\Events\CommandFailed;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

final class Kernel extends ConsoleKernel
{
    /**
     * @var array
     *
     * @phpstan-ignore-next-line missingType.iterableValue
     */
    protected $commands = [
        \App\Console\Commands\AuditDatabaseIndexesCommand::class,
        \App\Console\Commands\FixCodeStyleCommand::class,
        \App\Console\Commands\ValidateCodeStyleCommand::class,
        \App\Console\Commands\CodeStyleWatchCommand::class,
        \App\Console\Commands\DemonstrateTimeoutCommand::class,
        \App\Console\Commands\GenerateApiSpecCommand::class,
        \App\Console\Commands\GenerateReportsCommand::class,
        \App\Console\Commands\CheckRefreshDatabaseCommand::class,
        \App\Console\Commands\BackupPrepareCommand::class,
        \App\Console\Commands\BackupVerifyCommand::class,
        \App\Console\Commands\I18nAuditCommand::class,
        \App\Console\Commands\ValidateContractCommand::class,
        \App\Console\Commands\ReconcileInventoryCommand::class,
        \App\Console\Commands\SanitizeHtmlContentCommand::class,
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
                Log::error('Daily code style validation failed');
            });

        // Run code style fix weekly on Sundays at 3 AM
        $schedule
            ->command('code-style:fix --path=app --report')
            ->weeklyOn(0, '03:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->onSuccess(function (): void {
                Log::info('Weekly code style fix completed successfully');
            });

        $prepareSchedule = (array) config('backup.schedule.prepare', []);

        if (($prepareSchedule['enabled'] ?? true) === true) {
            $event = $schedule->command('backup:prepare');

            $prepareCron = $prepareSchedule['cron'] ?? null;
            $prepareAt = $prepareSchedule['at'] ?? null;

            if (is_string($prepareCron) && $prepareCron !== '') {
                $event->cron($prepareCron);
            } elseif (is_string($prepareAt) && $prepareAt !== '') {
                $event->dailyAt($prepareAt);
            } else {
                $event->daily();
            }

            $event
                ->withoutOverlapping()
                ->runInBackground()
                ->onOneServer()
                ->onFailure(static function (): void {
                    Log::error('Scheduled backup:prepare command failed');
                });
        }

        $verifySchedule = (array) config('backup.schedule.verify', []);

        if (($verifySchedule['enabled'] ?? true) === true) {
            $event = $schedule->command('backup:verify');

            $verifyCron = $verifySchedule['cron'] ?? null;
            $verifyAt = $verifySchedule['at'] ?? null;

            if (is_string($verifyCron) && $verifyCron !== '') {
                $event->cron($verifyCron);
            } elseif (is_string($verifyAt) && $verifyAt !== '') {
                $event->dailyAt($verifyAt);
            } else {
                $event->daily();
            }

            $event
                ->withoutOverlapping()
                ->runInBackground()
                ->onOneServer()
                ->onFailure(static function (): void {
                    Log::error('Scheduled backup:verify command failed');
                });
        }

        $schedule
            ->command('privacy:prune-audit-logs')
            ->daily()
            ->withoutOverlapping()
            ->runInBackground()
            ->onOneServer();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
