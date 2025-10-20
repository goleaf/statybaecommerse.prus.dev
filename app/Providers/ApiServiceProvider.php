<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class ApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('api.default', function (Request $request): array {
            return $this->buildApiLimits($request, 'default');
        });

        RateLimiter::for('api.notifications', function (Request $request): array {
            return $this->buildApiLimits($request, 'notifications');
        });

        RateLimiter::for('api.autocomplete', function (Request $request): array {
            return $this->buildApiLimits($request, 'autocomplete');
        });
    }

    /**
     * @return array<int, Limit>
     */
    private function buildApiLimits(Request $request, string $profile): array
    {
        $config = (array) config('security.rate_limiting.api.'.$profile, []);

        $limits = array_filter([
            $this->makeLimit($request, $config['user'] ?? null, $this->userRateLimitKey($request, $profile), $profile.'.user'),
            $this->makeLimit($request, $config['ip'] ?? null, $this->ipRateLimitKey($request, $profile), $profile.'.ip'),
            $this->makeLimit($request, $config['global'] ?? null, $this->globalRateLimitKey($profile), $profile.'.global'),
        ]);

        if ($limits === []) {
            return [$this->fallbackLimit($request, $profile)];
        }

        return array_values($limits);
    }

    private function makeLimit(Request $request, mixed $config, string $key, string $scope): ?Limit
    {
        if (! is_array($config)) {
            return null;
        }

        $maxAttempts = max(0, (int) ($config['max_attempts'] ?? 0));
        if ($maxAttempts <= 0) {
            return null;
        }

        $decaySeconds = max(1, (int) ($config['decay_seconds'] ?? 60));
        $minutes = max(1, (int) ceil($decaySeconds / 60));

        return Limit::perMinutes($minutes, $maxAttempts)
            ->by($key)
            ->response(fn (Request $incoming, array $headers) => $this->rateLimitResponse($incoming, $headers, $scope, $maxAttempts));
    }

    private function fallbackLimit(Request $request, string $profile): Limit
    {
        $scope = $profile.'.fallback';

        return Limit::perMinute(60)
            ->by($this->userRateLimitKey($request, $profile))
            ->response(fn (Request $incoming, array $headers) => $this->rateLimitResponse($incoming, $headers, $scope, 60));
    }

    private function rateLimitResponse(Request $request, array $headers, string $scope, int $maxAttempts): JsonResponse
    {
        $retryAfter = (int) ($headers['Retry-After'] ?? 0);

        if ($retryAfter <= 0 && isset($headers['X-RateLimit-Reset'])) {
            $retryAfter = max(0, (int) $headers['X-RateLimit-Reset'] - time());
        }

        $payload = [
            'message' => 'Too many requests. Please slow down.',
            'scope' => $scope,
            'limit' => $maxAttempts,
        ];

        if ($retryAfter > 0) {
            $payload['retry_after'] = $retryAfter;
        }

        return response()->json($payload, 429)->withHeaders($headers);
    }

    private function userRateLimitKey(Request $request, string $suffix): string
    {
        $userId = $request->user()?->getAuthIdentifier();
        if ($userId !== null) {
            return 'user:'.$userId.'|'.$suffix;
        }

        return $this->ipRateLimitKey($request, $suffix);
    }

    private function ipRateLimitKey(Request $request, string $suffix): string
    {
        $ip = $request->ip();
        $identifier = is_string($ip) && $ip !== '' ? $ip : 'unknown';

        return 'ip:'.$identifier.'|'.$suffix;
    }

    private function globalRateLimitKey(string $suffix): string
    {
        return 'global:'.$suffix;
    }
}
