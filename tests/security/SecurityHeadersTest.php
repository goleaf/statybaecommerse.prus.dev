<?php

declare(strict_types=1);

use App\Http\Middleware\AddSecurityHeaders;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

uses(TestCase::class);

it('adds the expected security headers to responses', function (): void {
    $middleware = app(AddSecurityHeaders::class);
    $request = Request::create('/security-headers-test', 'GET');

    $response = $middleware->handle($request, static fn (): \Symfony\Component\HttpFoundation\Response => new Response('ok'));

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('X-Frame-Options'))->toBe('DENY')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin')
        ->and($response->headers->get('Permissions-Policy'))->toBe('accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=(), fullscreen=(self), display-capture=()');

    $csp = $response->headers->get('Content-Security-Policy');
    expect($csp)->toBeString()
        ->and($csp)->toContain("default-src 'self'")
        ->and($csp)->toMatch("/script-src 'self' 'nonce-[^']+' https:\\/\\/unpkg\\.com 'unsafe-eval'/")
        ->and($csp)->toContain("script-src-attr 'unsafe-inline'")
        ->and($csp)->toMatch("/style-src 'self' 'nonce-[^']+' https:\\/\\/fonts\\.bunny\\.net https:\\/\\/unpkg\\.com 'unsafe-inline'/")
        ->and($csp)->toMatch("/style-src-attr 'self' 'unsafe-inline'/")
        ->and($csp)->toContain("font-src 'self' https://fonts.bunny.net data:")
        ->and($csp)->toContain("img-src 'self' data: blob:");
});

it('allows customizing CSP directives via configuration', function (): void {
    config()->set('security.headers.content_security_policy.directives.script-src', [
        "'self'",
        'https://trusted.cdn.example',
        "'unsafe-eval'",
    ]);

    $middleware = app(AddSecurityHeaders::class);
    $request = Request::create('/security-headers-custom', 'GET');

    $response = $middleware->handle($request, static fn (): \Symfony\Component\HttpFoundation\Response => new Response('ok'));

    $csp = $response->headers->get('Content-Security-Policy');
    expect($csp)->toBeString()
        ->and($csp)->toContain("script-src 'self' https://trusted.cdn.example");
});
