<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\VersionCompatibility\Security\RateLimiter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security middleware for version compatibility operations
 *
 * Provides additional security layers including:
 * - Request validation and sanitization
 * - Rate limiting enforcement
 * - Security headers injection
 * - Audit logging for security events
 */
final class VersionCompatibilitySecurityMiddleware
{
    public function __construct(
        private readonly RateLimiter $rateLimiter
    ) {}

    /**
     * Handle an incoming request with comprehensive security checks
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Apply rate limiting
        try {
            $this->rateLimiter->checkRateLimit($request);
        } catch (RuntimeException $e) {
            Log::channel('security')->warning('Rate limit exceeded in middleware', [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path'       => $request->path(),
            ]);

            return response()->json([
                'error'   => __('messages.too_many_requests'),
                'message' => $e->getMessage(),
            ], 429);
        }

        // Validate request parameters
        $this->validateRequestParameters($request);

        // Add security headers to response
        $response = $next($request);

        return $this->addSecurityHeaders($response);
    }

    /**
     * Validate request parameters for security
     */
    private function validateRequestParameters(Request $request): void
    {
        // Check for suspicious patterns in request data
        $suspiciousPatterns = [
            '/\.\.[\/\\\\]/',  // Path traversal
            '/<script[^>]*>/',  // XSS attempts
            '/javascript:/',    // JavaScript injection
            '/data:/',          // Data URI schemes
            '/vbscript:/',      // VBScript injection
        ];

        $requestData = json_encode($request->all());

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $requestData)) {
                Log::channel('security')->error('Suspicious request pattern detected', [
                    'pattern'      => $pattern,
                    'ip'           => $request->ip(),
                    'user_agent'   => $request->userAgent(),
                    'path'         => $request->path(),
                    'data_preview' => substr($requestData, 0, 200),
                ]);

                abort(400, __('messages.invalid_request_parameters'));
            }
        }
    }

    /**
     * Add security headers to response
     */
    private function addSecurityHeaders(Response $response): Response
    {
        $headers = [
            'X-Content-Type-Options'  => 'nosniff',
            'X-Frame-Options'         => 'DENY',
            'X-XSS-Protection'        => '1; mode=block',
            'Referrer-Policy'         => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';",
        ];

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
