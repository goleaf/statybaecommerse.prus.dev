<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Contracts\HealthReporter as HealthReporterContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_reports_ok_status(): void
    {
        config(['queue.default' => 'sync']);

        $response = $this->getJson('/api/v1/health');

        $response->assertOk();
        $cacheControl = $response->headers->get('Cache-Control');

        $this->assertIsString($cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
        $response->assertHeader('Pragma', 'no-cache');
        $response->assertJsonPath('status', 'ok');
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => [
                'database' => ['status', 'latency_ms'],
                'cache'    => ['status', 'latency_ms'],
                'disk'     => ['status', 'latency_ms'],
            ],
        ]);

        $checks = $response->json('checks');

        $this->assertIsArray($checks);
        $this->assertArrayNotHasKey('queue', $checks);
    }

    public function test_ready_endpoint_includes_queue_check_when_asynchronous_queue_configured(): void
    {
        config(['queue.default' => 'database']);

        $response = $this->getJson('/api/v1/ready');

        $response->assertOk();
        $response->assertJsonPath('status', 'ok');
        $response->assertJsonPath('checks.queue.status', 'ok');
        $this->assertSame('database', $response->json('checks.queue.meta.connection'));

        $checks = $response->json('checks');

        $this->assertIsArray($checks);
        $this->assertArrayHasKey('disk', $checks);
    }

    public function test_ready_endpoint_returns_service_unavailable_on_failed_check(): void
    {
        $fakeReporter = new class implements HealthReporterContract
        {
            public function report(bool $includeQueue = false): array
            {
                return [
                    'status'    => 'error',
                    'timestamp' => now()->toIso8601String(),
                    'checks'    => [
                        'database' => [
                            'status'     => 'failed',
                            'latency_ms' => 0.0,
                            'message'    => 'Simulated failure',
                        ],
                    ],
                ];
            }
        };

        $this->app->instance(HealthReporterContract::class, $fakeReporter);

        try {
            $response = $this->getJson('/api/v1/ready');
        } finally {
            $this->app->forgetInstance(HealthReporterContract::class);
        }

        $response->assertStatus(503);
        $cacheControl = $response->headers->get('Cache-Control');

        $this->assertIsString($cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
        $response->assertHeader('Pragma', 'no-cache');
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('checks.database.status', 'failed');
        $response->assertJsonPath('checks.database.message', 'Simulated failure');
    }
}
