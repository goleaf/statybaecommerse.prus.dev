<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Session\Session as SessionContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Throwable;

final class FeatureToggleService
{
    /**
     * Create a new service responsible for evaluating feature toggles.
     * Injecting the auth factory and session contract keeps the service
     * framework-aware while remaining easy to mock in tests.
     */
    public function __construct(
        private readonly AuthFactory $authFactory,
        private readonly SessionContract $session,
    ) {}

    /**
     * Determine whether the provided feature key should be considered enabled.
     * Database-backed flags are checked first to allow dynamic control, with
     * configuration-based fallbacks offering sensible defaults when no record
     * exists. Analytics for the evaluation are captured for observability.
     *
     * @param array<string, mixed> $context
     */
    public function isEnabled(string $featureKey, array $context = []): bool
    {
        // Resolve the current user and environment so targeting can be honoured.
        $user = $this->resolveUser($context);
        $environment = $context['environment'] ?? app()->environment();

        // Cache evaluations briefly to avoid hammering the database in loops.
        $cacheKey = sprintf(
            'feature-toggle:%s:%s:%s',
            $featureKey,
            $environment,
            $user?->getAuthIdentifier() ?? 'guest'
        );

        $enabled = Cache::remember($cacheKey, now()->addSeconds(30), function () use ($featureKey, $environment, $user, $context): bool {
            $flag = FeatureFlag::query()
                ->where('key', $featureKey)
                ->where(function ($query) use ($environment): void {
                    $query->whereNull('environment')->orWhere('environment', $environment);
                })
                ->first();

            if ($flag !== null) {
                // Respect explicit environment targeting before delegating to the model helper.
                if ($flag->environment !== null && $flag->environment !== $environment) {
                    return false;
                }

                $originalEnvironment = app()->environment();

                // Temporarily align the Laravel environment with the evaluated context so the
                // underlying helper honours staged rollouts without mutating global state.
                app()->detectEnvironment(static fn () => $environment);

                try {
                    return $flag->isEnabled($user);
                } finally {
                    app()->detectEnvironment(static fn () => $originalEnvironment);
                }
            }

            // Fall back to configuration metadata when no persisted flag is available.
            return $this->evaluateConfigFallback($featureKey, $environment, $user, $context);
        });

        $this->recordUsage($featureKey, $enabled, $user, $environment, $context);

        return $enabled;
    }

    /**
     * Retrieve the list of currency codes that should behave like zero-decimal
     * currencies for the active request. Environment and user-specific overrides
     * only apply when the supporting feature toggle is enabled, enabling gradual
     * rollout without impacting all customers at once.
     *
     * @param  array<string, mixed> $context
     * @return array<int, string>
     */
    public function getZeroDecimalCurrencies(array $context = []): array
    {
        $environment = $context['environment'] ?? app()->environment();
        $user = $this->resolveUser($context);

        // Defaults are always active to guarantee legacy behaviour stays intact.
        $currencies = Config::get('currency.zero_decimal_currencies.defaults', ['JPY']);

        if ($this->isEnabled($this->zeroDecimalFeatureKey(), array_merge($context, [
            'environment' => $environment,
            'user'        => $user,
        ]))) {
            // Merge in environment-specific overrides when the toggle is enabled.
            $environmentOverrides = Config::get("currency.zero_decimal_currencies.environments.{$environment}", []);
            $currencies = array_merge($currencies, $environmentOverrides);

            // Apply user-level targeting so beta testers can opt-in ahead of time.
            if ($user !== null) {
                $targetedIds = Config::get('currency.zero_decimal_currencies.user_targets.ids', []);
                $targetedEmails = array_map('strtolower', Config::get('currency.zero_decimal_currencies.user_targets.emails', []));

                if (in_array($user->getAuthIdentifier(), $targetedIds, true)) {
                    $currencies = array_merge($currencies, $environmentOverrides);
                }

                if (in_array(strtolower((string) $user->email), $targetedEmails, true)) {
                    $currencies = array_merge($currencies, $environmentOverrides);
                }
            }
        }

        // De-duplicate and re-index the result for predictable comparisons.
        return array_values(array_unique(array_filter($currencies)));
    }

    /**
     * Resolve the currency feature key from configuration to keep the enum
     * agnostic of configuration naming.
     */
    private function zeroDecimalFeatureKey(): string
    {
        return (string) Config::get('currency.features.zero_decimal_overrides.key', 'currency-zero-decimal-overrides');
    }

    /**
     * Resolve the user either from the context payload or the current guard.
     */
    private function resolveUser(array $context): ?User
    {
        if (isset($context['user']) && $context['user'] instanceof User) {
            return $context['user'];
        }

        $guard = $context['guard'] ?? null;

        return $this->authFactory->guard($guard)->user();
    }

    /**
     * Evaluate configuration fallbacks for a feature when no persisted flag is found.
     *
     * @param array<string, mixed> $context
     */
    private function evaluateConfigFallback(string $featureKey, string $environment, ?User $user, array $context): bool
    {
        // Honour the legacy app-features configuration so existing toggles continue working.
        $appFeature = Config::get("app-features.features.{$featureKey}");

        if ($appFeature !== null) {
            if ($appFeature instanceof \App\Support\FeatureState) {
                return $appFeature === \App\Support\FeatureState::Enabled;
            }

            if (is_string($appFeature)) {
                return Str::lower($appFeature) === Str::lower(\App\Support\FeatureState::Enabled->value);
            }

            if (is_bool($appFeature)) {
                return $appFeature;
            }
        }

        // Evaluate currency-specific metadata for the zero-decimal rollout.
        $currencyConfig = Config::get('currency.features.zero_decimal_overrides');

        if (is_array($currencyConfig) && $featureKey === $this->zeroDecimalFeatureKey()) {
            $enabled = (bool) Arr::get($currencyConfig, 'default_enabled', false);
            $environmentOverrides = Arr::get($currencyConfig, "environments.{$environment}");

            if ($environmentOverrides !== null) {
                $enabled = (bool) $environmentOverrides;
            }

            if ($enabled) {
                // Use deterministic hashing for gradual rollout percentages.
                $percentage = (int) Arr::get($currencyConfig, 'rollout_percentage', 100);
                if ($percentage < 100) {
                    $seed = (string) ($user?->getAuthIdentifier() ?? $context['session_id'] ?? $this->session->getId() ?? 'guest');
                    $hash = crc32($featureKey . '|' . $seed);
                    $bucket = ($hash % 100) + 1;
                    if ($bucket > $percentage) {
                        $enabled = false;
                    }
                }
            }

            return $enabled;
        }

        return false;
    }

    /**
     * Persist a lightweight analytics event describing how the feature evaluated.
     * Duplicate events for the same session are suppressed for a short window
     * so noisy loops (e.g., API polling) do not flood the telemetry tables.
     *
     * @param array<string, mixed> $context
     */
    private function recordUsage(string $featureKey, bool $enabled, ?User $user, string $environment, array $context): void
    {
        $analyticsConfig = Config::get('currency.analytics', []);
        $ttl = (int) ($analyticsConfig['cache_ttl'] ?? 300);

        $sessionId = (string) ($context['session_id'] ?? $this->session->getId() ?? 'system');
        $cacheKey = sprintf('feature-toggle-usage:%s:%s', $featureKey, $sessionId);

        if ($ttl > 0 && ! Cache::add($cacheKey, true, now()->addSeconds($ttl))) {
            return;
        }

        try {
            AnalyticsEvent::query()->create([
                'event_name'  => sprintf('%s.%s', $analyticsConfig['event_prefix'] ?? 'feature-toggle', $featureKey),
                'event_type'  => 'feature_toggle',
                'description' => sprintf('Feature "%s" evaluated as %s', $featureKey, $enabled ? 'enabled' : 'disabled'),
                'session_id'  => $sessionId,
                'user_id'     => $user?->getAuthIdentifier(),
                'properties'  => [
                    'environment' => $environment,
                    'result'      => $enabled ? 'enabled' : 'disabled',
                    'context'     => array_diff_key($context, array_flip(['user'])),
                ],
            ]);
        } catch (Throwable $exception) {
            // Swallow analytics persistence issues to avoid disrupting feature checks.
        }
    }
}
