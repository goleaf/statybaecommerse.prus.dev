<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\SecurityMonitoringService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security monitoring middleware for threat detection and response.
 */
final class SecurityMonitoring
{
    public function __construct(
        private readonly SecurityMonitoringService $securityService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Check if IP is blocked
        if ($this->securityService->shouldBlockIp($request->ip())) {
            return response()->json([
                'message'    => 'Access denied due to security policy.',
                'error_code' => 'IP_BLOCKED',
            ], 403);
        }

        // Monitor request for threats (non-blocking)
        $threats = $this->securityService->monitorRequest($request);

        // Add threat information to request for logging
        if (! empty($threats)) {
            $request->attributes->set('security_threats', $threats);
        }

        return $next($request);
    }
}
