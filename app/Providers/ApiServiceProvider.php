<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ApiKey;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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

        RateLimiter::for('api.profile', function (Request $request): Limit|array {
            return $this->resolveLimit(
                config('security.rate_limiting.api.profile', 60),
                $this->rateLimitKey($request, 'profile')
            );
        });

        RateLimiter::for('frontend.checkout', function (Request $request): Limit|array {
            return $this->resolveLimit(
                config('security.rate_limiting.frontend.checkout', 10),
                $this->rateLimitKey($request, 'checkout')
            );
        });

        RateLimiter::for('partner.api', function (Request $request): Limit|array {
            $apiKey = $this->resolvePartnerApiKey($request);

            if ($apiKey === null) {
                return Limit::perMinute(60)->by('partner_api:anonymous:' . $request->ip());
            }

            $key = $apiKey->rateLimiterKey();
            $limit = $apiKey->rate_limit;

            if ($limit === null || $limit <= 0) {
                return Limit::none()->by($key);
            }

            return Limit::perMinute(max(1, (int) $limit))->by($key);
        });
    }

    /**
     * @param  array{per_user:int|null,per_ip:int|null}  $config
     * @return array<int, Limit>
     */
    private function limitsFor(Request $request, string $bucket, array $config): array
    {
        $userId = $request->user()?->getAuthIdentifier();
        $key = $userId !== null ? 'user:' . $userId : 'ip:' . $request->ip();

        return $suffix === '' ? $key : $key . '|' . $suffix;
    }

    /**
     * @param  array<int|string, int>|int $limit
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

    private function resolvePartnerApiKey(Request $request): ?ApiKey
    {
        $resolved = $request->attributes->get('partner_api_key');
        if ($resolved instanceof ApiKey) {
            return $resolved;
        }

        $headerName = (string) config('services.partner_api.header', 'X-Api-Key');
        $header = $request->headers->get($headerName);
        if (! is_string($header)) {
            return null;
        }

        $trimmed = trim($header);
        if ($trimmed === '') {
            return null;
        }

        /** @var ApiKey|null $apiKey */
        $apiKey = ApiKey::query()
            ->where('key', ApiKey::hashKey($trimmed))
            ->first();

        return $apiKey;
    }
}
