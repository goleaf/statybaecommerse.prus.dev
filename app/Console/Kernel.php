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
use Illuminate\Support\Str;

final class Kernel extends ConsoleKernel
{
    /** @var array<string, OperationLog> */
    private array $commandOperations = [];

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
        \App\Console\Commands\ValidateContractCommand::class,
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
        $events = $this->app['events'];

        $events->listen(CommandStarting::class, function (CommandStarting $event): void {
            $logContext = $this->app->make(LogContext::class);
            $structuredLogger = $this->app->make(StructuredLogger::class);

            $logContext->setCorrelationId((string) Str::uuid());
            $logContext->setRequestId((string) Str::uuid());
            $logContext->setCommandName($event->command ?? $event->input->getFirstArgument() ?? 'artisan');
            $logContext->setUserId(null);
            $logContext->merge([
                'cli' => true,
            ]);

            \Log::withContext($logContext->toArray());

            $operation = $structuredLogger->operation('console_command', [
                'command' => $logContext->commandName(),
                'arguments' => $event->input->getArguments(),
                'options' => $event->input->getOptions(),
            ]);

            $this->commandOperations[spl_object_hash($event->input)] = $operation;
        });

        $events->listen(CommandFinished::class, function (CommandFinished $event): void {
            $key = spl_object_hash($event->input);

            if (! isset($this->commandOperations[$key])) {
                return;
            }

            $operation = $this->commandOperations[$key];
            $operation->finish([
                'exit_code' => $event->exitCode,
                'memory_peak_bytes' => memory_get_peak_usage(true),
            ]);

            unset($this->commandOperations[$key]);
        });

        $events->listen(CommandFailed::class, function (CommandFailed $event): void {
            $key = spl_object_hash($event->input);

            if (! isset($this->commandOperations[$key])) {
                return;
            }

            $operation = $this->commandOperations[$key];
            $operation->fail($event->exception, [
                'exit_code' => $event->exitCode,
            ]);
        });

        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
