<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ApiKey;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            // Use a dedicated bucket for the default throttle so runtime overrides do not share state
            // with the read limiter, which previously caused higher read limits to mask stricter
            // default constraints (as seen in the profile endpoint regression).
            return $this->layeredLimits($request, 'api.default', $this->defaultRateLimitConfig());
        });

        RateLimiter::for('api.read', function (Request $request): array {
            return $this->layeredLimits($request, 'api.read', $this->readRateLimitConfig());
        });

        RateLimiter::for('api.write', function (Request $request): array {
            return $this->layeredLimits($request, 'api.write', $this->writeRateLimitConfig());
        });

        RateLimiter::for('api.notifications.read', function (Request $request): array {
            return $this->layeredLimits($request, 'api.notifications.read', $this->notificationRateLimitConfig('read'));
        });

        RateLimiter::for('api.notifications.write', function (Request $request): array {
            return $this->layeredLimits($request, 'api.notifications.write', $this->notificationRateLimitConfig('write'));
        });

        RateLimiter::for('api.autocomplete', function (Request $request): array {
            return $this->layeredLimits($request, 'api.autocomplete', $this->autocompleteRateLimitConfig(), 'autocomplete');
        });

        RateLimiter::for('api.profile', function (Request $request): array {
            return $this->layeredLimits($request, 'api.profile', $this->profileRateLimitConfig());
        });

        RateLimiter::for('api.exports', function (Request $request): array {
            return $this->layeredLimits($request, 'api.exports', $this->exportRateLimitConfig(), 'exports');
        });

        RateLimiter::for('frontend.checkout', function (Request $request): array {
            return $this->layeredLimits($request, 'frontend.checkout', $this->checkoutRateLimitConfig());
        });

        RateLimiter::for('partner.api', function (Request $request): array {
            $apiKey = $this->resolvePartnerApiKey($request);

            if ($apiKey === null) {
                $ip = (string) $request->ip();
                $identifier = $ip !== '' ? $ip : 'unknown';
                $key = 'partner_api:anonymous:' . $identifier;

                return [$this->buildLimit('partner.api.anonymous', $key, 60)];
            }

            $key = $apiKey->rateLimiterKey();
            $limit = $apiKey->rate_limit;

            if ($limit === null || $limit <= 0) {
                return [Limit::none()->by($key)];
            }

            return [$this->buildLimit('partner.api', $key, max(1, (int) $limit))];
        });
    }

    /**
     * Compose layered limits for a logical bucket.
     *
     * @param  array{per_user:int|null,per_ip:int|null} $config
     * @return array<int, Limit>
     */
    private function layeredLimits(Request $request, string $bucket, array $config, ?string $keySuffix = null): array
    {
        $suffix = $keySuffix ?? $bucket;

        // Compose per-user and per-IP throttles so we can block abusive actors individually.
        $limits = [
            $this->perUserLimit($request, $bucket, $config['per_user'], $suffix),
            $this->perIpLimit($request, $bucket, $config['per_ip'], $suffix),
        ];

        $filtered = array_values(array_filter($limits, static fn (?Limit $limit): bool => $limit !== null));

        if ($filtered !== []) {
            return $filtered;
        }

        $fallback = $this->defaultRateLimitConfig();

        return array_values(array_filter([
            $this->perUserLimit($request, $bucket, $fallback['per_user'], $suffix),
            $this->perIpLimit($request, $bucket, $fallback['per_ip'], $suffix),
        ], static fn (?Limit $limit): bool => $limit !== null));
    }

    /**
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function readRateLimitConfig(): array
    {
        $config = config('security.rate_limiting.api.read');

        return $this->normalizeRateLimitConfig($config, $this->defaultRateLimitConfig());
    }

    /**
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function writeRateLimitConfig(): array
    {
        $config = config('security.rate_limiting.api.write');

        return $this->normalizeRateLimitConfig($config, $this->defaultRateLimitConfig());
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
            $maxAttempts = $this->normalizeLimitValue(
                $value['max_attempts'] ?? $value['limit'] ?? $value['attempts'] ?? null,
                null,
            );

            if ($maxAttempts === null) {
                return null;
            }

            $decaySeconds = null;

            foreach (['decay_seconds' => 1, 'decay' => 1] as $field => $multiplier) {
                if (array_key_exists($field, $value) && is_numeric($value[$field])) {
                    $seconds = (int) $value[$field] * $multiplier;
                    if ($seconds > 0) {
                        $decaySeconds = $seconds;
                        break;
                    }
                }
            }

            if ($decaySeconds === null && array_key_exists('decay_minutes', $value) && is_numeric($value['decay_minutes'])) {
                $minutes = (int) $value['decay_minutes'];
                if ($minutes > 0) {
                    $decaySeconds = $minutes * 60;
                }
            }

            if ($decaySeconds === null && array_key_exists('decay_hours', $value) && is_numeric($value['decay_hours'])) {
                $hours = (int) $value['decay_hours'];
                if ($hours > 0) {
                    $decaySeconds = $hours * 3600;
                }
            }

            if ($decaySeconds === null && array_key_exists('decay_days', $value) && is_numeric($value['decay_days'])) {
                $days = (int) $value['decay_days'];
                if ($days > 0) {
                    $decaySeconds = $days * 86400;
                }
            }

            if ($decaySeconds !== null) {
                return Limit::perSecond($maxAttempts, $decaySeconds)->by($key);
            }

            $value = $maxAttempts;
        }

        $maxAttempts = $this->normalizeLimitValue($value, null);

        if ($maxAttempts === null) {
            return null;
        }

        $scopeName = is_string($scope) ? strtolower(trim($scope)) : 'minute';
        $window = 1;

        if (str_contains($scopeName, ':')) {
            [$scopeName, $windowSegment] = explode(':', (string) $scopeName, 2);
            $windowCandidate = trim($windowSegment);

            if ($windowCandidate !== '' && is_numeric($windowCandidate)) {
                $window = max(1, (int) $windowCandidate);
            }
        }

        return match ($scopeName) {
            'second', 'seconds', 'per_second', 'per-second' => Limit::perSecond($maxAttempts, $window)->by($key),
            'minute', 'minutes', 'per_minute', 'per-minute' => Limit::perMinutes($window, $maxAttempts)->by($key),
            'hour', 'hours', 'per_hour', 'per-hour' => Limit::perHour($maxAttempts, $window)->by($key),
            'day', 'days', 'per_day', 'per-day' => Limit::perDay($maxAttempts, $window)->by($key),
            'none', 'unlimited' => Limit::none()->by($key),
            default => null,
        };

    }

    /**
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function notificationRateLimitConfig(string $type): array
    {
        $config = data_get(config('security.rate_limiting.api.notifications'), $type);

        return $this->normalizeRateLimitConfig($config, $this->defaultRateLimitConfig());
    }

    /**
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function autocompleteRateLimitConfig(): array
    {
        $config = config('security.rate_limiting.api.autocomplete');

        return $this->normalizeRateLimitConfig($config, [
            'per_user' => 30,
            'per_ip'   => 30,
        ]);
    }

    /**
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function profileRateLimitConfig(): array
    {
        $config = config('security.rate_limiting.api.profile');

        return $this->normalizeRateLimitConfig($config, $this->readRateLimitConfig());
    }

    /**
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function exportRateLimitConfig(): array
    {
        $config = config('security.rate_limiting.api.exports');

        return $this->normalizeRateLimitConfig($config, $this->defaultRateLimitConfig());
    }

    /**
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function checkoutRateLimitConfig(): array
    {
        $config = config('security.rate_limiting.frontend.checkout');

        return $this->normalizeRateLimitConfig($config, [
            'per_user' => 10,
            'per_ip'   => 10,
        ]);
    }

    /**
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function defaultRateLimitConfig(): array
    {
        $baseline = max(1, (int) config('security.rate_limiting.defaults.minute', 60));
        $config = config('security.rate_limiting.api.default');

        if (! is_array($config)) {
            $value = $this->normalizeLimitValue($config, $baseline);

            return [
                'per_user' => $value,
                'per_ip'   => $value,
            ];
        }

        return $this->normalizeRateLimitConfig($config, [
            'per_user' => $baseline,
            'per_ip'   => $baseline,
        ]);
    }

    /**
     * @param  array<string, mixed>                     $config
     * @param  array{per_user:int|null,per_ip:int|null} $fallback
     * @return array{per_user:int|null,per_ip:int|null}
     */
    private function normalizeRateLimitConfig(mixed $config, array $fallback): array
    {
        if ($config === null) {
            return $fallback;
        }

        if (! is_array($config)) {
            return [
                'per_user' => $this->normalizeLimitValue($config, $fallback['per_user']),
                'per_ip'   => $this->normalizeLimitValue($config, $fallback['per_ip']),
            ];
        }

        return [
            'per_user' => array_key_exists('per_user', $config)
                ? $this->normalizeLimitValue($config['per_user'], $fallback['per_user'])
                : $fallback['per_user'],
            'per_ip' => array_key_exists('per_ip', $config)
                ? $this->normalizeLimitValue($config['per_ip'], $fallback['per_ip'])
                : $fallback['per_ip'],
        ];
    }

    private function normalizeLimitValue(mixed $value, ?int $fallback): ?int
    {
        if ($value === null) {
            return null;
        }

        if (! is_numeric($value)) {
            return $fallback;
        }

        $normalized = (int) $value;

        if ($normalized <= 0) {
            return null;
        }

        return $normalized;
    }

    private function perUserLimit(Request $request, string $bucket, ?int $maxAttempts, ?string $keySuffix = null): ?Limit
    {
        if ($maxAttempts === null || $maxAttempts <= 0) {
            return null;
        }

        $userId = $this->resolveAuthenticatedIdentifier($request);

        if ($userId === null) {
            return null;
        }

        $key = $this->formatKey('user', (string) $userId, $keySuffix ?? $bucket);

        return $this->buildLimit($bucket . '.user', $key, $maxAttempts);
    }

    /**
     * Resolve the best-fit authenticated identifier across available guards.
     */
    private function resolveAuthenticatedIdentifier(Request $request): string|int|null
    {
        // Always start with the user that might already be attached to the request instance.
        $user = $request->user();

        if ($user !== null) {
            $identifier = $user->getAuthIdentifier();

            if (is_string($identifier) || is_int($identifier)) {
                return $identifier;
            }
        }

        // Iterate over configured guards to cover token-based authentication like Sanctum.
        $defaultGuard = config('auth.defaults.guard');

        foreach (array_keys(config('auth.guards', [])) as $guard) {
            // Skip the default guard because it has already been evaluated above.
            if ($guard === $defaultGuard) {
                continue;
            }

            $guardUser = $request->user($guard);

            if ($guardUser === null) {
                continue;
            }

            $identifier = $guardUser->getAuthIdentifier();

            if (is_string($identifier) || is_int($identifier)) {
                return $identifier;
            }
        }

        // Fall back to the global authentication manager as a final safeguard.
        $authId = Auth::id();

        if (is_string($authId) || is_int($authId)) {
            return $authId;
        }

        return null;
    }

    private function perIpLimit(Request $request, string $bucket, ?int $maxAttempts, ?string $keySuffix = null): ?Limit
    {
        if ($maxAttempts === null || $maxAttempts <= 0) {
            return null;
        }

        $ip = (string) $request->ip();
        $identifier = $ip !== '' ? $ip : 'unknown';
        $key = $this->formatKey('ip', $identifier, $keySuffix ?? $bucket);

        return $this->buildLimit($bucket . '.ip', $key, $maxAttempts);
    }

    private function buildLimit(string $name, string $key, int $maxAttempts): Limit
    {
        return Limit::perMinute($maxAttempts)
            ->by($key)
            ->response(function (Request $request, array $headers) use ($name, $key, $maxAttempts): SymfonyResponse {
                // Emit a structured warning whenever a throttle threshold is exceeded.
                $this->logRateLimitExceeded($request, $name, $key, $maxAttempts);

                return $this->tooManyRequestsResponse($request, $headers);
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
            'rate_limiter'   => $name,
            'rate_limit_key' => $key,
            'max_attempts'   => $maxAttempts,
            'request_method' => $request->method(),
            'request_path'   => $request->path(),
            'route_name'     => $route !== null ? $route->getName() : null,
            'user_id'        => $request->user()?->getAuthIdentifier(),
            'ip_address'     => $request->ip(),
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
                $request->attributes->set('correlation_id', $resolved);

                return $resolved;
            }
        }

        $generated = Str::uuid()->toString();
        $request->attributes->set('correlation_id', $generated);

        return $generated;
    }

    /**
     * @param array<string, int|string> $headers
     */
    private function tooManyRequestsResponse(Request $request, array $headers): SymfonyResponse
    {
        $response = response()
            ->json([
                'error' => [
                    'code'    => 'rate_limit_exceeded',
                    'message' => __('Too many requests.'),
                ],
            ], SymfonyResponse::HTTP_TOO_MANY_REQUESTS)
            ->withHeaders($headers);

        $headerName = config('app.correlation_header', 'X-Correlation-ID');

        return $response->header($headerName, $this->resolveCorrelationId($request));
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
