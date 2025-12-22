<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security enhancement middleware for request tracking and additional security measures.
 */
final class SecurityEnhancement
{
    public function handle(Request $request, Closure $next): Response
    {
        // Add request ID for tracking and correlation
        if (!$request->hasHeader('X-Request-ID')) {
            $requestId = $this->generateRequestId();
            $request->headers->set('X-Request-ID', $requestId);
        }

        // Add security context to request
        $request->attributes->set('security_context', [
            'request_id' => $request->header('X-Request-ID'),
            'ip_address' => $request->ip(),
            'user_agent' => $this->sanitizeUserAgent($request->userAgent()),
            'timestamp' => now()->toISOString(),
        ]);

        /** @var Response $response */
        $response = $next($request);

        // Add security headers to response
        $this->addSecurityHeaders($response, $request);

        return $response;
    }

    /**
     * Generate a unique request ID for tracking.
     */
    private function generateRequestId(): string
    {
        return 'req_' . Str::random(16) . '_' . time();
    }

    /**
     * Sanitize user agent to prevent log injection.
     */
    private function sanitizeUserAgent(?string $userAgent): string
    {
        if ($userAgent === null) {
            return 'unknown';
        }

        // Remove control characters and limit length
        $sanitized = preg_replace('/[\x00-\x1F\x7F]/', '', $userAgent) ?? 'unknown';
        
        return substr($sanitized, 0, 200);
    }

    /**
     * Add additional security headers to the response.
     */
    private function addSecurityHeaders(Response $response, Request $request): void
    {
        // Add request ID to response for tracking
        $response->headers->set('X-Request-ID', $request->header('X-Request-ID'));

        // Add Content Security Policy for admin routes
        if ($request->is('admin/*') || $request->is('filament/*')) {
            $csp = $this->buildContentSecurityPolicy();
            $response->headers->set('Content-Security-Policy', $csp);
        }

        // Add additional security headers
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow', false);
        
        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff', false);
        
        // Add cache control for sensitive pages
        if ($request->is('admin/*') || $request->is('api/*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
        }
    }

    /**
     * Build Content Security Policy for admin areas.
     */
    private function buildContentSecurityPolicy(): string
    {
        $nonce = base64_encode(random_bytes(16));
        
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' 'strict-dynamic'",
            "style-src 'self' 'unsafe-inline'", // Filament requires inline styles
            "img-src 'self' data: blob: https:",
            "font-src 'self' data:",
            "connect-src 'self'",
            "media-src 'self'",
            "object-src 'none'",
            "child-src 'none'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests",
        ];

        // Store nonce for use in views
        request()->attributes->set('csp_nonce', $nonce);

        return implode('; ', $policies);
    }
}