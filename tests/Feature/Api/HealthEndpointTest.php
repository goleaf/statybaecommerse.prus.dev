<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

final class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_reports_successful_checks(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store')
            ->assertJsonStructure([
                'status',
                'timestamp',
                'version' => ['hash'],
                'checks' => [
                    'database' => ['status'],
                    'cache' => ['status'],
                    'queue' => ['status', 'optional'],
                ],
            ])
            ->assertJson(['status' => 'ok']);
    }

    public function test_ready_endpoint_returns_service_unavailable_when_database_is_down(): void
    {
        DB::shouldReceive('connection')->andThrow(new RuntimeException('DB connection failed.'));

        $response = $this->getJson('/api/v1/ready');

        $response
            ->assertStatus(503)
            ->assertHeader('Cache-Control', 'no-store')
            ->assertJsonPath('checks.database.status', 'failed')
            ->assertJsonPath('status', 'error');
    }
}
