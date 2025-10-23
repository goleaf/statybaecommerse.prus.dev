<?php

declare(strict_types=1);

namespace App\Providers;

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
        RateLimiter::for('api.default', function (Request $request): array {
            return $this->limitsFor($request, 'api.read', $this->readRateLimitConfig());
        });

        RateLimiter::for('api.read', function (Request $request): array {
            return $this->limitsFor($request, 'api.read', $this->readRateLimitConfig());
        });

        RateLimiter::for('api.write', function (Request $request): array {
            return $this->limitsFor($request, 'api.write', $this->writeRateLimitConfig());
        });

        RateLimiter::for('api.notifications.read', function (Request $request): array {
            return $this->limitsFor($request, 'api.notifications.read', $this->notificationRateLimitConfig('read'));
        });

        RateLimiter::for('api.notifications.write', function (Request $request): array {
            return $this->limitsFor($request, 'api.notifications.write', $this->notificationRateLimitConfig('write'));
        });

        RateLimiter::for('api.autocomplete', function (Request $request): array {
            return $this->limitsFor($request, 'api.autocomplete', $this->autocompleteRateLimitConfig());
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
}
