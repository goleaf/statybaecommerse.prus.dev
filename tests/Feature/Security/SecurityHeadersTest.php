<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test security headers and middleware functionality.
 */
class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_adds_security_headers_to_responses(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /** @test */
    public function it_adds_hsts_header_when_enabled(): void
    {
        config(['security.headers.hsts.enabled' => true]);
        config(['security.headers.hsts.max_age' => 31536000]);
        config(['security.headers.hsts.include_subdomains' => true]);

        $response = $this->get('/');

        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    /** @test */
    public function it_adds_permissions_policy_header(): void
    {
        $response = $this->get('/');

        $response->assertHeaderContains('Permissions-Policy', 'camera=()');
        $response->assertHeaderContains('Permissions-Policy', 'microphone=()');
        $response->assertHeaderContains('Permissions-Policy', 'geolocation=()');
    }

    /** @test */
    public function it_adds_request_id_to_responses(): void
    {
        $response = $this->withMiddleware([
            \App\Http\Middleware\SecurityEnhancement::class,
        ])->get('/');

        $response->assertHeader('X-Request-ID');

        $requestId = $response->headers->get('X-Request-ID');
        $this->assertStringStartsWith('req_', $requestId);
    }

    /** @test */
    public function it_adds_csp_header_for_admin_routes(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->withMiddleware([\App\Http\Middleware\SecurityEnhancement::class])
            ->get('/admin');

        $response->assertHeader('Content-Security-Policy');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContains("default-src 'self'", $csp);
        $this->assertStringContains("object-src 'none'", $csp);
        $this->assertStringContains("frame-ancestors 'none'", $csp);
    }

    /** @test */
    public function it_adds_cache_control_for_sensitive_pages(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->withMiddleware([\App\Http\Middleware\SecurityEnhancement::class])
            ->get('/admin');

        $response->assertHeader('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->assertHeader('Pragma', 'no-cache');
    }

    /** @test */
    public function it_sanitizes_user_agent_in_security_context(): void
    {
        $maliciousUserAgent = "Mozilla/5.0\n[FAKE LOG ENTRY] Admin access granted\nReal browser";

        $response = $this->withHeaders(['User-Agent' => $maliciousUserAgent])
            ->withMiddleware([\App\Http\Middleware\SecurityEnhancement::class])
            ->get('/');

        // Should not contain newlines that could inject fake log entries
        $request = $this->app['request'];
        $securityContext = $request->attributes->get('security_context');

        $this->assertArrayHasKey('user_agent', $securityContext);
        $this->assertStringNotContainsString("\n", $securityContext['user_agent']);
        $this->assertStringNotContainsString('FAKE LOG ENTRY', $securityContext['user_agent']);
    }

    /** @test */
    public function it_limits_user_agent_length(): void
    {
        $longUserAgent = str_repeat('A', 500);

        $response = $this->withHeaders(['User-Agent' => $longUserAgent])
            ->withMiddleware([\App\Http\Middleware\SecurityEnhancement::class])
            ->get('/');

        $request = $this->app['request'];
        $securityContext = $request->attributes->get('security_context');

        $this->assertLessThanOrEqual(200, strlen($securityContext['user_agent']));
    }

    /** @test */
    public function it_handles_missing_user_agent_gracefully(): void
    {
        $response = $this->withoutMiddleware()
            ->withMiddleware([\App\Http\Middleware\SecurityEnhancement::class])
            ->get('/');

        $request = $this->app['request'];
        $securityContext = $request->attributes->get('security_context');

        $this->assertEquals('unknown', $securityContext['user_agent']);
    }

    /** @test */
    public function it_preserves_existing_request_id(): void
    {
        $existingRequestId = 'custom_req_12345';

        $response = $this->withHeaders(['X-Request-ID' => $existingRequestId])
            ->withMiddleware([\App\Http\Middleware\SecurityEnhancement::class])
            ->get('/');

        $response->assertHeader('X-Request-ID', $existingRequestId);
    }

    /** @test */
    public function it_includes_security_context_in_request(): void
    {
        $response = $this->withMiddleware([\App\Http\Middleware\SecurityEnhancement::class])
            ->get('/');

        $request = $this->app['request'];
        $securityContext = $request->attributes->get('security_context');

        $this->assertIsArray($securityContext);
        $this->assertArrayHasKey('request_id', $securityContext);
        $this->assertArrayHasKey('ip_address', $securityContext);
        $this->assertArrayHasKey('user_agent', $securityContext);
        $this->assertArrayHasKey('timestamp', $securityContext);
    }

    /** @test */
    public function it_respects_security_headers_configuration(): void
    {
        config(['security.headers.enabled' => false]);

        $response = $this->get('/');

        // Should not have security headers when disabled
        $response->assertHeaderMissing('X-Frame-Options');
        $response->assertHeaderMissing('X-Content-Type-Options');
    }

    /** @test */
    public function it_adds_robots_tag_to_prevent_indexing(): void
    {
        $response = $this->withMiddleware([\App\Http\Middleware\SecurityEnhancement::class])
            ->get('/');

        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }
}
