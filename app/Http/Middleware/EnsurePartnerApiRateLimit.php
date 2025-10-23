<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePartnerApiRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->attributes->get('partner_api_key');

        if (! $apiKey instanceof ApiKey) {
            return $next($request);
        }

        $limit = $apiKey->rate_limit;

        if ($limit === null || $limit <= 0) {
            return $next($request);
        }

        $rateLimiterKey = $apiKey->rateLimiterKey();
        $decaySeconds = (int) config('services.partner_api.rate_limit.decay_seconds', 60);

        if (RateLimiter::tooManyAttempts($rateLimiterKey, $limit)) {
            $retryAfter = RateLimiter::availableIn($rateLimiterKey);

            return $this->reject($retryAfter, $limit);
        }

        RateLimiter::hit($rateLimiterKey, $decaySeconds);

        /** @var Response $response */
        $response = $next($request);

        $remaining = RateLimiter::remaining($rateLimiterKey, $limit);
        $response->headers->set('X-RateLimit-Limit', (string) $limit);
        $response->headers->set('X-RateLimit-Remaining', (string) max(0, $remaining));

        return $response;
    }

    /**
     * Emit a throttling response with explicit retry metadata.
     */
    private function reject(int $retryAfter, int $limit): JsonResponse
    {
        $response = response()->json([
            'message' => 'Partner API rate limit exceeded.',
        ], Response::HTTP_TOO_MANY_REQUESTS);

        $response->headers->set('Retry-After', (string) max(1, $retryAfter));
        $response->headers->set('X-RateLimit-Limit', (string) $limit);
        $response->headers->set('X-RateLimit-Remaining', '0');

        return $response;
    }
}
