<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

final class PartnerApiRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('partner_api_key');

        $limitConfig = config('services.partner_api.rate_limit');
        $maxAttempts = (int) data_get($limitConfig, 'max_attempts', 60);
        $decaySeconds = (int) data_get($limitConfig, 'decay_seconds', 60);

        $signature = $this->resolveSignature($request, $apiKey);

        if (RateLimiter::tooManyAttempts($signature, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($signature);

            $response = $this->buildThrottleResponse($retryAfter, $maxAttempts);
            $response->headers->set('X-RateLimit-Remaining', '0');

            return $response;
        }

        RateLimiter::hit($signature, $decaySeconds);

        /** @var Response $response */
        $response = $next($request);

        $remaining = RateLimiter::remaining($signature, $maxAttempts);
        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) max($remaining, 0));

        return $response;
    }

    private function resolveSignature(Request $request, ?ApiKey $apiKey): string
    {
        $parts = [
            'partner-api',
            $apiKey?->getKey() ?? 'guest',
            $request->ip() ?? 'unknown',
        ];

        return implode(':', $parts);
    }

    private function buildThrottleResponse(int $retryAfter, int $maxAttempts): JsonResponse
    {
        $response = response()->json([
            'message' => 'Too Many Requests.',
        ], 429);

        $response->headers->set('Retry-After', (string) $retryAfter);
        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);

        return $response;
    }
}
