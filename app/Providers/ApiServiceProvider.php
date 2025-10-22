<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ApiKey;
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

        RateLimiter::for('api.profile', function (Request $request): array {
            return $this->limitsFor('security.rate_limiting.api.profile', $request, 'profile');
        });

        RateLimiter::for('frontend.checkout', function (Request $request): array {
            return $this->limitsFor('security.rate_limiting.frontend.checkout', $request, 'checkout');
        });

        RateLimiter::for('partner.api', function (Request $request): array {
            $apiKey = $this->resolvePartnerApiKey($request);

            if ($apiKey === null) {
                return [Limit::perMinute(60)->by('partner_api:anonymous:' . $request->ip())];
            }

            $key = $apiKey->rateLimiterKey();
            $limit = $apiKey->rate_limit;

            if ($limit === null || $limit <= 0) {
                return [Limit::none()->by($key)];
            }

            return [Limit::perMinute(max(1, (int) $limit))->by($key)];
        });
    }

    private function rateLimitKey(Request $request, string $suffix = ''): string
    {
        $userId = $request->user()?->getAuthIdentifier();
        $key = $userId !== null ? 'user:' . $userId : 'ip:' . $request->ip();

        return $suffix === '' ? $key : $key . '|' . $suffix;
    }

    /**
     * @return array<int, Limit>
     */
    private function limitsFor(string $configKey, Request $request, string $suffix = '', ?int $fallback = null): array
    {
        $definitions = config($configKey, []);
        $key = $this->rateLimitKey($request, $suffix);

        if (is_int($definitions)) {
            return [Limit::perMinute(max(1, $definitions))->by($key)];
        }

        if (! is_array($definitions) || $definitions === []) {
            $default = (int) ($fallback ?? config('security.rate_limiting.defaults.minute', 60));

            // Fall back to the global default minute window when the dedicated configuration is missing.
            return [Limit::perMinute(max(1, $default))->by($key)];
        }

        $limits = $this->resolveLimit($definitions, $key);

        if ($limits === []) {
            $default = (int) ($fallback ?? config('security.rate_limiting.defaults.minute', 60));

            // Reuse the default limit whenever none of the configured scopes produced a valid limiter.
            return [Limit::perMinute(max(1, $default))->by($key)];
        }

        return $limits;
    }

    /**
     * Normalize a limit definition array into the concrete RateLimiter Limit objects.
     *
     * @param array<int|string, mixed> $definitions
     * @return array<int, Limit>
     */
    private function resolveLimit(array $definitions, string $key): array
    {
        $limits = [];

        foreach ($definitions as $scope => $value) {
            $limit = $this->normalizeLimit($scope, $value, $key);

            if ($limit !== null) {
                $limits[] = $limit;
            }
        }

        return $limits;
    }

    /**
     * Convert a single scope definition into a Limit instance when valid.
     */
    private function normalizeLimit(int|string $scope, mixed $value, string $key): ?Limit
    {
        if (is_array($value)) {
            $maxAttempts = (int) ($value['max_attempts'] ?? $value['max'] ?? 0);
            $decayMinutes = (int) ($value['decay_minutes'] ?? $value['decay'] ?? 0);

            if ($maxAttempts > 0 && $decayMinutes > 0) {
                return Limit::perMinutes($decayMinutes, $maxAttempts)->by($key);
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
            'minute', 'minutes', 'per_minute' => Limit::perMinute($maxAttempts)->by($key),
            'second', 'seconds', 'per_second' => Limit::perSeconds($maxAttempts, 1)->by($key),
            'hour', 'hours', 'per_hour' => Limit::perHour($maxAttempts)->by($key),
            'day', 'days', 'per_day', 'daily' => Limit::perDay($maxAttempts)->by($key),
            'week', 'weeks', 'per_week', 'weekly' => Limit::perMinutes(10080, $maxAttempts)->by($key),
            'month', 'months', 'per_month', 'monthly' => Limit::perMinutes(43200, $maxAttempts)->by($key),
            default => null,
        };
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
