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

        RateLimiter::for('api.exports', function (Request $request): Limit|array {
            return $this->resolveLimit(
                config('security.rate_limiting.api.exports', 30),
                $this->rateLimitKey($request, 'exports')
            );
        });

        RateLimiter::for('partner.api', function (Request $request): Limit|array {
            $apiKey = $this->resolvePartnerApiKey($request);

            if ($apiKey === null) {
                return Limit::perMinute(60)->by('partner_api:anonymous:'.$request->ip());
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
        $limits = [
            $this->perUserLimit($request, $bucket, $config['per_user']),
            $this->perIpLimit($request, $bucket, $config['per_ip']),
        ];

        return array_values(array_filter($limits, static fn (?Limit $limit): bool => $limit !== null));
    }

    /**
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function readRateLimitConfig(): array
    {
        /** @var array<string, mixed> $config */
        $config = (array) config('security.rate_limiting.api.read', []);

        return $this->normalizeRateLimitConfig(
            $config,
            (int) config('security.rate_limiting.api.default', 60),
        );
    }

    /**
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function writeRateLimitConfig(): array
    {
        /** @var array<string, mixed> $config */
        $config = (array) config('security.rate_limiting.api.write', []);

        return $this->normalizeRateLimitConfig(
            $config,
            (int) config('security.rate_limiting.api.default', 60),
        );
    }

    /**
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function notificationRateLimitConfig(string $type): array
    {
        /** @var array<string, mixed> $config */
        $config = (array) data_get(config('security.rate_limiting.api.notifications', []), $type, []);

        return $this->normalizeRateLimitConfig(
            $config,
            (int) config('security.rate_limiting.api.default', 60),
        );
    }

    /**
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function autocompleteRateLimitConfig(): array
    {
        /** @var array<string, mixed> $config */
        $config = (array) config('security.rate_limiting.api.autocomplete', []);

        return $this->normalizeRateLimitConfig(
            $config,
            30,
        );
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function normalizeRateLimitConfig(array $config, int $fallback): array
    {
        $perUser = array_key_exists('per_user', $config) ? $this->normalizeLimitValue($config['per_user'], $fallback) : $fallback;
        $perIp = array_key_exists('per_ip', $config) ? $this->normalizeLimitValue($config['per_ip'], $fallback) : $fallback;

        return [
            'per_user' => $perUser,
            'per_ip' => $perIp,
        ];
    }

    private function normalizeLimitValue(mixed $value, int $fallback): int
    {
        $normalized = is_numeric($value) ? (int) $value : $fallback;

        return $normalized > 0 ? $normalized : $fallback;
    }

    private function perUserLimit(Request $request, string $bucket, ?int $maxAttempts): ?Limit
    {
        if ($maxAttempts === null || $maxAttempts <= 0) {
            return null;
        }

        $userId = $request->user()?->getAuthIdentifier();

        if (! is_string($userId) && ! is_int($userId)) {
            return null;
        }

        $key = $this->formatKey('user', (string) $userId, $bucket);

        return $this->buildLimit($bucket.'.user', $key, $maxAttempts);
    }

    private function perIpLimit(Request $request, string $bucket, ?int $maxAttempts): ?Limit
    {
        if ($maxAttempts === null || $maxAttempts <= 0) {
            return null;
        }

        $ip = (string) $request->ip();
        $identifier = $ip !== '' ? $ip : 'unknown';
        $key = $this->formatKey('ip', $identifier, $bucket);

        return $this->buildLimit($bucket.'.ip', $key, $maxAttempts);
    }

    private function buildLimit(string $name, string $key, int $maxAttempts): Limit
    {
        return Limit::perMinute($maxAttempts)
            ->by($key)
            ->response(function (Request $request, array $headers) use ($name, $key, $maxAttempts): SymfonyResponse {
                $this->logRateLimitExceeded($request, $name, $key, $maxAttempts);

                return $this->tooManyRequestsResponse($headers);
            });
    }

    private function formatKey(string $type, string $identifier, string $bucket): string
    {
        return sprintf('%s:%s|%s', $type, $identifier, $bucket);
    }

    private function logRateLimitExceeded(Request $request, string $name, string $key, int $maxAttempts): void
    {
        $route = $request->route();

        Log::warning('API request throttled.', [
            'correlation_id' => $this->resolveCorrelationId($request),
            'rate_limiter' => $name,
            'rate_limit_key' => $key,
            'max_attempts' => $maxAttempts,
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'route_name' => $route !== null ? $route->getName() : null,
            'user_id' => $request->user()?->getAuthIdentifier(),
            'ip_address' => $request->ip(),
        ]);
    }

    private function resolveCorrelationId(Request $request): string
    {
        $attribute = $request->attributes->get('correlation_id');

        if (is_string($attribute) && $attribute !== '') {
            return $attribute;
        }

        if (app()->bound('request_correlation_id')) {
            $resolved = app()->make('request_correlation_id');

            if (is_string($resolved) && $resolved !== '') {
                return $resolved;
            }
        }

        return Str::uuid()->toString();
    }

    /**
     * @param  array<string, int|string>  $headers
     */
    private function tooManyRequestsResponse(array $headers): SymfonyResponse
    {
        return response()
            ->json([
                'error' => [
                    'code' => 'rate_limit_exceeded',
                    'message' => __('Too many requests.'),
                ],
            ], SymfonyResponse::HTTP_TOO_MANY_REQUESTS)
            ->withHeaders($headers);
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
