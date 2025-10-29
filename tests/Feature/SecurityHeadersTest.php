<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_applied_to_api_responses(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk();

        $headers = (array) config('security.headers.values');

        foreach ($headers as $name => $value) {
            $response->assertHeader($name, $value);
        }

    }

    public function test_security_headers_can_be_disabled(): void
    {
        config(['security.headers.enabled' => false]);

        $response = $this->getJson('/api/v1/health');

        $response->assertOk();

        foreach (array_keys((array) config('security.headers.values')) as $name) {
            $response->assertHeaderMissing($name);
        }

    }
}
