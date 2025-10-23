<?php

declare(strict_types=1);

namespace Tests\Feature\Logging;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use function collect;

final class CommandLoggingTest extends TestCase
{
    public function test_command_logs_include_correlation_and_metrics(): void
    {
        Storage::fake('local');

        $records = collect();
        Log::listen(function (MessageLogged $event) use (&$records): void {
            $records = $records->push($event);
        });

        Artisan::call('reports:generate', [
            '--type' => 'system',
            '--output' => 'reports/test',
            '--format' => 'json',
        ]);

        $finish = $records->first(function (MessageLogged $event) {
            return ($event->context['operation'] ?? null) === 'reports_generate_command'
                && ($event->context['event'] ?? null) === 'finish';
        });

        $this->assertNotNull($finish, 'Expected a finish log entry for the command.');
        $this->assertArrayHasKey('correlation_id', $finish->context);
        $this->assertNotEmpty($finish->context['correlation_id']);

        $metrics = $finish->context['metrics'] ?? [];
        $this->assertArrayHasKey('reports_generated', $metrics);
        $this->assertArrayHasKey('duration_seconds', $metrics);

        $commandOperation = $records->first(function (MessageLogged $event) use ($finish) {
            return ($event->context['operation'] ?? null) === 'console_command'
                && ($event->context['event'] ?? null) === 'finish'
                && ($event->context['correlation_id'] ?? null) === ($finish->context['correlation_id'] ?? null);
        });

        $this->assertNotNull($commandOperation, 'Expected console command lifecycle logs to share the correlation ID.');
        $this->assertArrayHasKey('metrics', $commandOperation->context);
        $this->assertSame(0, $commandOperation->context['metrics']['exit_code'] ?? null);
    }
}
