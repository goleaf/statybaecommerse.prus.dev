<?php

declare(strict_types=1);

namespace Tests\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Provide a lightweight HTTP smoke test so tooling can rely on a
 * deterministic response payload from the public health endpoint.
 */
final class HealthEndpointSmokeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_reports_the_health_endpoint(): void
    {
        // Exercise the shared health endpoint so infrastructure monitors retain a smoke test.
        $response = $this->getJson('/api/v1/health');

        // Confirm the API reports a healthy status code and payload structure.
        $response->assertOk()->assertJsonStructure(['status']);
    }
}
