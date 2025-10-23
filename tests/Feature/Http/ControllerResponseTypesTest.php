<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use Tests\TestCase;

class ControllerResponseTypesTest extends TestCase
{
    public function test_robots_endpoint_returns_ok(): void
    {
        $response = $this->get(route('robots'));

        $response->assertOk();
    }

    public function test_api_health_returns_json(): void
    {
        $response = $this->getJson(route('api.v1.health'));

        $response->assertOk()->assertJsonStructure([
            'status',
            'checks',
            'timestamp',
        ]);
    }

    public function test_admin_root_redirects_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect();
    }
}

