<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Throwable;

/**
 * Service for managing feature toggles and their dependencies
 */
class FeatureToggleService
{
    private const CURRENCY_ZERO_DECIMAL_FEATURE_KEY = 'currency-zero-decimal-overrides';

    /**
     * Check if a feature is enabled
     */
    public function isEnabled(string $feature, array $context = []): bool
    {
        if ($feature === self::CURRENCY_ZERO_DECIMAL_FEATURE_KEY) {
            return $this->isCurrencyZeroDecimalOverridesEnabled($context);
        }

        return (bool) Config::get("app-features.features.{$feature}", false);
    }

    /**
     * Get all enabled features
     */
    public function getEnabledFeatures(): array
    {
        return array_filter(
            Config::get('app-features.features', []),
            static fn ($enabled): bool => $enabled === true
        );
    }

    /**
     * Resolve the list of zero-decimal currencies for the current context.
     *
     * @param  array<string, mixed> $context
     * @return list<string>
     */
    public function getZeroDecimalCurrencies(array $context = []): array
    {
        $defaults = $this->normalizeCurrencyCodes(Config::get('currency.zero_decimal_currencies.defaults', []));

        if (! $this->isEnabled(self::CURRENCY_ZERO_DECIMAL_FEATURE_KEY, $context)) {
            return $defaults;
        }

        $environment = $this->resolveEnvironment($context);
        $overrides = $this->normalizeCurrencyCodes(
            Config::get("currency.zero_decimal_currencies.environments.{$environment}", [])
        );

        return array_values(array_unique([...$defaults, ...$overrides]));
    }

    /**
     * Check if campaigns feature is enabled (for backward compatibility)
     *
     * @deprecated Campaigns feature has been permanently removed
     */
    public function isCampaignsEnabled(): bool
    {
        return false; // Campaigns feature permanently disabled
    }

    /**
     * Get features that depend on campaigns
     */
    public function getCampaignDependentFeatures(): array
    {
        return [
            'discount'        => $this->isEnabled('discount'),
            'customer_groups' => $this->isEnabled('customer_groups'),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    private function isCurrencyZeroDecimalOverridesEnabled(array $context): bool
    {
        $environment = $this->resolveEnvironment($context);
        $subjectKey = $this->resolveRolloutSubjectKey($context);
        $cacheKey = $this->buildCurrencyFeatureCacheKey($environment, $subjectKey);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($context, $environment): bool {
            $databaseDecision = $this->evaluateCurrencyFeatureFromDatabase($environment, $context);
            if ($databaseDecision !== null) {
                return $databaseDecision;
            }

            return $this->evaluateCurrencyFeatureFromConfig($environment, $context);
        });
    }

    /**
     * @param array<string, mixed> $context
     */
    private function resolveEnvironment(array $context): string
    {
        $environment = Arr::get($context, 'environment');

        if (is_string($environment) && $environment !== '') {
            return $environment;
        }

        return app()->environment();
    }

    /**
     * @param array<string, mixed> $context
     */
    private function resolveRolloutSubjectKey(array $context): string
    {
        $user = Arr::get($context, 'user');
        if ($user instanceof User && $user->getKey() !== null) {
            return 'user:' . $user->getKey();
        }

        $sessionId = Arr::get($context, 'session_id');
        if (is_scalar($sessionId) && trim((string) $sessionId) !== '') {
            return 'session:' . trim((string) $sessionId);
        }

        return 'guest';
    }

    /**
     * @param array<string, mixed> $context
     */
    private function evaluateCurrencyFeatureFromConfig(string $environment, array $context): bool
    {
        $defaultEnabled = (bool) Config::get('currency.features.zero_decimal_overrides.default_enabled', false);
        $environmentEnabled = (bool) Config::get(
            "currency.features.zero_decimal_overrides.environments.{$environment}",
            $defaultEnabled
        );

        if (! $environmentEnabled) {
            return false;
        }

        $rolloutPercentage = (float) Config::get('currency.features.zero_decimal_overrides.rollout_percentage', 100);

        return $this->passesRollout(
            featureKey: self::CURRENCY_ZERO_DECIMAL_FEATURE_KEY,
            rolloutPercentage: $rolloutPercentage,
            subjectKey: $this->resolveRolloutSubjectKey($context),
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    private function evaluateCurrencyFeatureFromDatabase(string $environment, array $context): ?bool
    {
        try {
            $flags = FeatureFlag::query()
                ->withoutGlobalScopes()
                ->where('key', self::CURRENCY_ZERO_DECIMAL_FEATURE_KEY)
                ->where('is_active', true)
                ->where('is_enabled', true)
                ->where(function ($query) use ($environment): void {
                    $query->whereNull('environment')->orWhere('environment', $environment);
                })
                ->orderByDesc('priority')
                ->orderByDesc('id')
                ->get();
        } catch (QueryException|Throwable) {
            // Tests that bootstrap without feature_flags migrations should still
            // fall back to configuration safely.
            return null;
        }

        if ($flags->isEmpty()) {
            return null;
        }

        $now = now();
        $subjectKey = $this->resolveRolloutSubjectKey($context);

        foreach ($flags as $flag) {
            if ($flag->starts_at !== null && $now->lt($flag->starts_at)) {
                continue;
            }

            if ($flag->ends_at !== null && $now->gt($flag->ends_at)) {
                continue;
            }

            $rollout = is_array($flag->rollout_percentage)
                ? (float) ($flag->rollout_percentage['percentage'] ?? 100)
                : 100.0;

            if (! $this->passesRollout(self::CURRENCY_ZERO_DECIMAL_FEATURE_KEY, $rollout, $subjectKey)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function passesRollout(string $featureKey, float $rolloutPercentage, string $subjectKey): bool
    {
        if ($rolloutPercentage <= 0) {
            return false;
        }

        if ($rolloutPercentage >= 100) {
            return true;
        }

        $hash = hash('sha256', "{$featureKey}|{$subjectKey}");
        $bucket = hexdec(substr($hash, 0, 8)) / 0xFFFFFFFF * 100;

        return $bucket < $rolloutPercentage;
    }

    private function buildCurrencyFeatureCacheKey(string $environment, string $subjectKey): string
    {
        $version = $this->resolveFeatureVersion(self::CURRENCY_ZERO_DECIMAL_FEATURE_KEY);

        return sprintf(
            'feature:%s:env:%s:subject:%s:v:%s',
            self::CURRENCY_ZERO_DECIMAL_FEATURE_KEY,
            $environment,
            sha1($subjectKey),
            $version
        );
    }

    private function resolveFeatureVersion(string $featureKey): string
    {
        try {
            $latest = FeatureFlag::query()
                ->withoutGlobalScopes()
                ->where('key', $featureKey)
                ->max('updated_at');
        } catch (QueryException|Throwable) {
            return 'no-table';
        }

        if ($latest === null) {
            return 'none';
        }

        $asString = (string) $latest;

        return $asString !== '' ? sha1($asString) : 'unknown';
    }

    /**
     * @return list<string>
     */
    private function normalizeCurrencyCodes(mixed $codes): array
    {
        if (! is_array($codes)) {
            return [];
        }

        $normalized = [];

        foreach ($codes as $code) {
            if (! is_scalar($code)) {
                continue;
            }

            $normalizedCode = strtoupper(trim((string) $code));

            if ($normalizedCode === '') {
                continue;
            }

            $normalized[] = $normalizedCode;
        }

        return array_values(array_unique($normalized));
    }
}
