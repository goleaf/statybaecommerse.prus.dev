<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class ApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('api.default', function (Request $request): array {
            return $this->limitsFor('security.rate_limiting.api.default', $request);
        });

        RateLimiter::for('api.notifications', function (Request $request): array {
            return $this->limitsFor('security.rate_limiting.api.notifications', $request, 'notifications');
        });

        RateLimiter::for('api.autocomplete', function (Request $request): array {
            return $this->limitsFor('security.rate_limiting.api.autocomplete', $request, 'autocomplete');
        });
    }

    private function rateLimitKey(Request $request, string $suffix = ''): string
    {
        $userId = $request->user()?->getAuthIdentifier();
        $key = $userId !== null ? 'user:'.$userId : 'ip:'.$request->ip();

        return $suffix === '' ? $key : $key.'|'.$suffix;
    }

    /**
     * @return array<int, Limit>
     */
    private function limitsFor(string $configKey, Request $request, string $suffix = ''): array
    {
        $definitions = config($configKey, []);
        $key = $this->rateLimitKey($request, $suffix);

        if (! is_array($definitions) || $definitions === []) {
            return [Limit::perMinute((int) config('security.rate_limiting.defaults.minute', 60))->by($key)];
        }

        $limits = [];

        foreach ($definitions as $scope => $value) {
            $limit = $this->resolveLimit($scope, $value, $key);

            if ($limit !== null) {
                $limits[] = $limit;
            }
        }

        if ($limits === []) {
            return [Limit::perMinute(60)->by($key)];
        }

        return $limits;
    }

    private function resolveLimit(int|string $scope, mixed $value, string $key): ?Limit
    {
        if (is_array($value)) {
            $maxAttempts = (int) ($value['max_attempts'] ?? $value['max'] ?? 0);
            $decayMinutes = (int) ($value['decay_minutes'] ?? $value['decay'] ?? 0);

            if ($maxAttempts > 0 && $decayMinutes > 0) {
                return Limit::perMinutes($maxAttempts, $decayMinutes)->by($key);
            }

            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $maxAttempts = (int) $value;

        if ($maxAttempts <= 0) {
            return null;
        }

        if (! is_string($scope) || $scope === '') {
            return Limit::perMinute($maxAttempts)->by($key);
        }

        return match (strtolower($scope)) {
            'minute', 'per_minute' => Limit::perMinute($maxAttempts)->by($key),
            'second', 'per_second' => Limit::perSeconds($maxAttempts, 1)->by($key),
            'hour', 'per_hour' => Limit::perHour($maxAttempts)->by($key),
            'day', 'per_day', 'daily' => Limit::perDay($maxAttempts)->by($key),
            default => null,
        };
    }
}
