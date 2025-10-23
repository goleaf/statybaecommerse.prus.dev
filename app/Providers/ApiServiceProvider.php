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
        RateLimiter::for('api.default', function (Request $request): Limit {
            return Limit::perMinute((int) config('security.rate_limiting.api.default', 60))
                ->by($this->rateLimitKey($request));
        });

        RateLimiter::for('api.notifications', function (Request $request): Limit {
            return Limit::perMinute((int) config('security.rate_limiting.api.notifications', 60))
                ->by($this->rateLimitKey($request, 'notifications'));
        });

        RateLimiter::for('api.autocomplete', function (Request $request): Limit {
            return Limit::perMinute((int) config('security.rate_limiting.api.autocomplete', 30))
                ->by($this->rateLimitKey($request, 'autocomplete'));
        });

        RateLimiter::for('api.exports', function (Request $request): Limit {
            return Limit::perMinute((int) config('api.rate_limits.exports', 10))
                ->by($this->rateLimitKey($request, 'exports'));
        });
    }

    private function rateLimitKey(Request $request, string $suffix = ''): string
    {
        $userId = $request->user()?->getAuthIdentifier();
        $key = $userId !== null ? 'user:'.$userId : 'ip:'.$request->ip();

        return $suffix === '' ? $key : $key.'|'.$suffix;
    }
}
