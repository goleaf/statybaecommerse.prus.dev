<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePartnerApiRateLimit
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pipeline = (string) $request->attributes->get('partner_api_pipeline', 'modern');

        if ($pipeline === 'legacy') {
            return $this->handleLegacy($request, $next);
        }

        $apiKey = $request->attributes->get('partner_api_key');

        if (! $apiKey instanceof ApiKey) {
            return $next($request);
        }

        $limit = $apiKey->rate_limit;

        if ($limit === null || $limit <= 0) {
            return $next($request);
        }

        $rateLimiterKey = $apiKey->rateLimiterKey();
        $configuredDecay = config('services.partner_api.rate_limit.decay_seconds', 60);
        $decaySeconds = is_numeric($configuredDecay) ? (int) $configuredDecay : 60;

        if (RateLimiter::tooManyAttempts($rateLimiterKey, $limit)) {
            $retryAfter = RateLimiter::availableIn($rateLimiterKey);

            // Respond with a 429 payload so clients know when they can retry.
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
     * Handle legacy partner API rate limiting semantics for hashed key integrations.
     *
     * @param Closure(Request): Response $next
     */
    private function handleLegacy(Request $request, Closure $next): Response
    {
        $apiKey = $request->attributes->get('partner_api_key');

        if (! $apiKey instanceof ApiKey) {
            return $next($request);
        }

        $limitConfig = config('services.partner_api.rate_limit', []);
        $maxAttempts = (int) data_get($limitConfig, 'max_attempts', 60);
        $decaySeconds = (int) data_get($limitConfig, 'decay_seconds', 60);

        if ($maxAttempts <= 0) {
            return $next($request);
        }

        $signature = $this->resolveLegacySignature($request, $apiKey);

        if (RateLimiter::tooManyAttempts($signature, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($signature);

            return $this->reject($retryAfter, $maxAttempts);
        }

        RateLimiter::hit($signature, $decaySeconds);

        /** @var Response $response */
        $response = $next($request);

        $remaining = RateLimiter::remaining($signature, $maxAttempts);
        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) max(0, $remaining));

        return $response;
    }

    private function resolveLegacySignature(Request $request, ApiKey $apiKey): string
    {
        $parts = [
            'partner-api',
            (string) $apiKey->getKey(),
            (string) ($request->ip() ?? 'unknown'),
        ];

        return implode(':', $parts);
    }

    private function reject(int $retryAfter, int $limit): Response
    {
        $response = response()->json([
            'message' => 'Partner API rate limit exceeded.',
        ], Response::HTTP_TOO_MANY_REQUESTS);

        $response->headers->set('Retry-After', (string) max(1, $retryAfter));
        $response->headers->set('X-RateLimit-Limit', (string) $limit);
        $response->headers->set('X-RateLimit-Remaining', '0');

        // Return the response directly so Laravel does not treat it as an unhandled exception.
        return $response;
    }
}
