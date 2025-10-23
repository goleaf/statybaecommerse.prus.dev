<?php

declare(strict_types=1);

use App\Contracts\HealthReporter as HealthReporterContract;

it('returns ok health payload with cache-control headers', function (): void {
    $response = $this->get('/api/v1/health');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'status',
        'checks' => ['database', 'cache', 'disk'],
        'timestamp',
    ]);
    $response->assertHeader('Cache-Control');
    expect($response->headers->get('Cache-Control'))
        ->toBeString()
        ->toContain('no-store')
        ->toContain('max-age=0');
    $response->assertHeader('Pragma');
});

it('returns 503 when reporter indicates failure', function (): void {
    $fake = new class implements HealthReporterContract {
        public function report(bool $includeQueue = false): array
        {
            return [
                'status' => 'error',
                'checks' => [
                    'database' => ['status' => 'failed', 'latency_ms' => 0.12, 'message' => 'boom'],
                    'cache' => ['status' => 'ok', 'latency_ms' => 0.01],
                    'disk' => ['status' => 'ok', 'latency_ms' => 0.02],
                ],
                'timestamp' => now()->toIso8601String(),
            ];
        }
    };

    app()->instance(HealthReporterContract::class, $fake);

    $response = $this->get('/api/v1/health');

    $response->assertStatus(503);
    $response->assertJsonPath('status', 'error');
});
