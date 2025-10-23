<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

final class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_security_headers_are_applied(): void
    {
        $response = $this->get('/login');

        $response->assertOk();

        $response->assertHeader('X-Frame-Options', config('security.headers.x_frame_options'));
        $response->assertHeader('X-Content-Type-Options', config('security.headers.x_content_type_options'));
        $response->assertHeader('Referrer-Policy', config('security.headers.referrer_policy'));
        $response->assertHeader('Permissions-Policy', config('security.headers.permissions_policy'));
        $response->assertHeader('Content-Security-Policy-Report-Only', config('security.headers.csp_report_only'));
    }
}
