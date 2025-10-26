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

        $expectedPolicy = collect((array) config('security.headers.content_security_policy'))
            ->map(function ($sources, string $directive): ?string {
                if (! is_string($directive) || $directive === '') {
                    return null;
                }

                $sources = is_string($sources) ? [$sources] : (is_array($sources) ? $sources : []);
                $sources = array_values(array_unique(array_filter($sources, fn ($value): bool => is_string($value) && $value !== '')));

                if ($sources === []) {
                    return null;
                }

                return $directive . ' ' . implode(' ', $sources);
            })
            ->filter()
            ->implode('; ');

        if ($expectedPolicy !== '') {
            $response->assertHeader('Content-Security-Policy', $expectedPolicy);
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

        $response->assertHeaderMissing('Content-Security-Policy');
    }
}
