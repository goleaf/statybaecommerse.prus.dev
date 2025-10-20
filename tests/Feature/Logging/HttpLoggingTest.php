<?php

declare(strict_types=1);

namespace Tests\Feature\Logging;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use function collect;

final class HttpLoggingTest extends TestCase
{
    public function test_request_logs_include_correlation_id(): void
    {
        $records = collect();

        Log::listen(function (MessageLogged $event) use (&$records): void {
            $records = $records->push($event);
        });

        $correlationId = 'test-correlation-'.uniqid('', true);

        $response = $this->withHeader('X-Correlation-ID', $correlationId)->get('/up');

        $response->assertOk();
        $response->assertHeader('X-Correlation-ID', $correlationId);

        $start = $records->first(function (MessageLogged $event) use ($correlationId) {
            return ($event->context['correlation_id'] ?? null) === $correlationId
                && ($event->context['event'] ?? null) === 'start';
        });

        $finish = $records->first(function (MessageLogged $event) use ($correlationId) {
            return ($event->context['correlation_id'] ?? null) === $correlationId
                && ($event->context['event'] ?? null) === 'finish';
        });

        $this->assertNotNull($start, 'Expected a start log with the provided correlation ID.');
        $this->assertSame('http_request', $start->context['operation'] ?? null);

        $this->assertNotNull($finish, 'Expected a finish log with the provided correlation ID.');
        $this->assertSame('http_request', $finish->context['operation'] ?? null);
        $this->assertArrayHasKey('duration_ms', $finish->context);
    }
}
