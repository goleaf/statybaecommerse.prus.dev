<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\AnalyticsEvent;
use App\Models\FeatureFlag;
use App\Models\User;
use App\Services\FeatureToggleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FeatureToggleServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_honours_environment_scoped_feature_flags(): void
    {
        // Freeze time so scheduled feature windows remain deterministic.
        Carbon::setTestNow('2025-02-01 12:00:00');

        $user = User::factory()->create();

        // Create a staging-only feature flag with full rollout and no additional conditions.
        FeatureFlag::factory()->create([
            'name'               => 'Zero Decimal Rollout',
            'key'                => 'currency-zero-decimal-overrides',
            'environment'        => 'staging',
            'is_active'          => true,
            'is_enabled'         => true,
            'conditions'         => null,
            'rollout_percentage' => ['percentage' => 100],
            'starts_at'          => Carbon::now()->subDay(),
            'ends_at'            => Carbon::now()->addDay(),
        ]);

        $service = $this->app->make(FeatureToggleService::class);

        // Staging environment should respect the active toggle.
        self::assertTrue($service->isEnabled('currency-zero-decimal-overrides', [
            'environment' => 'staging',
            'user'        => $user,
        ]));

        // Production should remain disabled because no matching flag exists there.
        self::assertFalse($service->isEnabled('currency-zero-decimal-overrides', [
            'environment' => 'production',
            'user'        => $user,
        ]));

        Carbon::setTestNow(null);
    }

    #[Test]
    public function it_merges_environment_overrides_when_feature_is_enabled(): void
    {
        Cache::flush();
        session()->put('feature_test', true);

        // Ensure configuration allows the fallback evaluation to enable the feature.
        Config::set('currency.features.zero_decimal_overrides.default_enabled', true);
        Config::set('currency.features.zero_decimal_overrides.environments.staging', true);
        Config::set('currency.features.zero_decimal_overrides.rollout_percentage', 100);
        Config::set('currency.zero_decimal_currencies.defaults', ['JPY']);
        Config::set('currency.zero_decimal_currencies.environments.staging', ['JPY', 'KRW']);

        $service = $this->app->make(FeatureToggleService::class);

        $currencies = $service->getZeroDecimalCurrencies(['environment' => 'staging']);

        // Defaults and staging overrides should both be present once the feature is active.
        self::assertContains('JPY', $currencies);
        self::assertContains('KRW', $currencies);
        self::assertCount(2, array_unique($currencies));

        // Analytics should capture the evaluation once thanks to the internal cache guard.
        self::assertSame(1, AnalyticsEvent::query()->count());
    }

    #[Test]
    public function it_respects_rollout_percentage_when_no_user_is_authenticated(): void
    {
        Cache::flush();
        Config::set('currency.features.zero_decimal_overrides.default_enabled', true);
        Config::set('currency.features.zero_decimal_overrides.environments.production', true);
        Config::set('currency.features.zero_decimal_overrides.rollout_percentage', 0);

        $service = $this->app->make(FeatureToggleService::class);

        // Provide a deterministic session identifier to seed the rollout hash.
        $result = $service->isEnabled('currency-zero-decimal-overrides', [
            'environment' => 'production',
            'session_id'  => 'test-session-id',
        ]);

        // A zero percent rollout should always disable the feature for anonymous traffic.
        self::assertFalse($result);
    }

    #[Test]
    public function it_refreshes_cached_results_after_feature_flag_changes(): void
    {
        // Reset cache state and override configuration so the fallback path returns false.
        Cache::flush();
        Config::set('currency.features.zero_decimal_overrides.default_enabled', false);
        Config::set('currency.features.zero_decimal_overrides.environments.staging', false);

        $service = $this->app->make(FeatureToggleService::class);
        $user = User::factory()->create();

        // Prime the cache by evaluating the feature before any database flag exists.
        self::assertFalse($service->isEnabled('currency-zero-decimal-overrides', [
            'environment' => 'staging',
            'user'        => $user,
        ]));

        // Create a staging-scoped flag that should flip the feature on for the same context.
        FeatureFlag::factory()->create([
            'name'               => 'Staging Rollout',
            'key'                => 'currency-zero-decimal-overrides',
            'environment'        => 'staging',
            'is_active'          => true,
            'is_enabled'         => true,
            'rollout_percentage' => ['percentage' => 100],
            'conditions'         => null,
            'starts_at'          => now()->subMinute(),
            'ends_at'            => now()->addHour(),
        ]);

        // The cached result should be invalidated automatically and now return true.
        self::assertTrue($service->isEnabled('currency-zero-decimal-overrides', [
            'environment' => 'staging',
            'user'        => $user,
        ]));
    }
}
