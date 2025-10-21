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
        RateLimiter::for('api.default', function (Request $request): Limit|array {
            return $this->resolveLimit(
                config('security.rate_limiting.api.default', 60),
                $this->rateLimitKey($request)
            );
        });

        RateLimiter::for('api.notifications', function (Request $request): Limit|array {
            return $this->resolveLimit(
                config('security.rate_limiting.api.notifications', 60),
                $this->rateLimitKey($request, 'notifications')
            );
        });

        RateLimiter::for('api.autocomplete', function (Request $request): Limit|array {
            return $this->resolveLimit(
                config('security.rate_limiting.api.autocomplete', 30),
                $this->rateLimitKey($request, 'autocomplete')
            );
        });
    }

    private function rateLimitKey(Request $request, string $suffix = ''): string
    {
        $userId = $request->user()?->getAuthIdentifier();
        $key = $userId !== null ? 'user:'.$userId : 'ip:'.$request->ip();

        return $suffix === '' ? $key : $key.'|'.$suffix;
    }

    /**
     * @param array<int|string, int>|int $limit
     * @return Limit|array<int, Limit>
     */
    private function resolveLimit(array|int $limit, string $key): Limit|array
    {
        if (! is_array($limit)) {
            return Limit::perMinute(max(1, (int) $limit))->by($key);
        }

        $limits = [];

        foreach ($limit as $interval => $maxAttempts) {
            $decayMinutes = $this->resolveDecayMinutes($interval);

            $limits[] = Limit::perMinutes($decayMinutes, max(1, (int) $maxAttempts))->by($key);
        }

        return $limits;
    }

    private function resolveDecayMinutes(int|string $interval): int
    {
        if (is_int($interval)) {
            return max(1, $interval);
        }

        $normalized = strtolower(trim($interval));

        return match ($normalized) {
            'minute', 'minutes' => 1,
            'hour', 'hours' => 60,
            'day', 'days' => 1440,
            'week', 'weeks' => 10080,
            'month', 'months' => 43200,
            default => $this->fallbackDecayMinutes($normalized),
        };
    }

    private function fallbackDecayMinutes(string $interval): int
    {
        $numeric = (int) $interval;

        return $numeric > 0 ? $numeric : 1;
    }
}
