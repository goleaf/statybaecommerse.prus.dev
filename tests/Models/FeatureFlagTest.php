<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_orders_feature_flags_by_name_using_scope(): void
    {
        // Create feature flags with deterministic names to test ordering logic.
        $gammaFlag = FeatureFlag::factory()->create([
            'name'        => 'Gamma Flag',
            'key'         => 'gamma-flag',
            'is_active'   => true,
            'is_enabled'  => true,
            'environment' => null,
        ]);
        $alphaFlag = FeatureFlag::factory()->create([
            'name'        => 'Alpha Flag',
            'key'         => 'alpha-flag',
            'is_active'   => true,
            'is_enabled'  => true,
            'environment' => null,
        ]);
        $betaFlag = FeatureFlag::factory()->create([
            'name'        => 'Beta Flag',
            'key'         => 'beta-flag',
            'is_active'   => true,
            'is_enabled'  => true,
            'environment' => null,
        ]);

        // Retrieve the names using the orderedByName scope to verify alphabetical sorting.
        $orderedNames = FeatureFlag::query()->orderedByName()->pluck('name')->all();

        // Assert that the alphabetical order matches the expected sequence.
        $this->assertSame([
            $alphaFlag->name,
            $betaFlag->name,
            $gammaFlag->name,
        ], $orderedNames);
    }

    #[Test]
    public function it_filters_active_feature_flags(): void
    {
        // Create both an active and inactive flag to test the active scope behaviour.
        $activeFlag = FeatureFlag::factory()->create([
            'name'        => 'Active Flag',
            'key'         => 'active-flag',
            'is_active'   => true,
            'is_enabled'  => true,
            'environment' => null,
        ]);
        FeatureFlag::factory()->create([
            'name'        => 'Inactive Flag',
            'key'         => 'inactive-flag',
            'is_active'   => false,
            'is_enabled'  => true,
            'environment' => null,
        ]);

        // Retrieve IDs of active flags using the scope to ensure filtering is correct.
        $activeIds = FeatureFlag::query()->active()->pluck('id')->all();

        // Assert that only the active flag is returned by the scope.
        $this->assertSame([$activeFlag->id], $activeIds);
    }

    #[Test]
    public function it_filters_enabled_feature_flags(): void
    {
        // Create enabled and disabled flags to exercise the enabled scope.
        $enabledFlag = FeatureFlag::factory()->create([
            'name'        => 'Enabled Flag',
            'key'         => 'enabled-flag',
            'is_active'   => true,
            'is_enabled'  => true,
            'environment' => null,
        ]);
        FeatureFlag::factory()->create([
            'name'        => 'Disabled Flag',
            'key'         => 'disabled-flag',
            'is_active'   => true,
            'is_enabled'  => false,
            'environment' => null,
        ]);

        // Retrieve IDs of enabled flags using the scope to validate filtering.
        $enabledIds = FeatureFlag::query()->enabled()->pluck('id')->all();

        // Assert that only the enabled flag appears in the result set.
        $this->assertSame([$enabledFlag->id], $enabledIds);
    }

    #[Test]
    public function it_filters_feature_flags_by_environment(): void
    {
        // Create feature flags for different environments to test the environment scope.
        $productionFlag = FeatureFlag::factory()->create([
            'name'        => 'Production Flag',
            'key'         => 'production-flag',
            'environment' => 'production',
            'is_active'   => true,
            'is_enabled'  => true,
        ]);
        FeatureFlag::factory()->create([
            'name'        => 'Staging Flag',
            'key'         => 'staging-flag',
            'environment' => 'staging',
            'is_active'   => true,
            'is_enabled'  => true,
        ]);

        // Retrieve IDs of production flags to confirm the scope narrows results correctly.
        $productionIds = FeatureFlag::query()->environment('production')->pluck('id')->all();

        // Assert that the production scope returns the expected flag only.
        $this->assertSame([$productionFlag->id], $productionIds);
    }

    #[Test]
    public function it_determines_if_a_feature_is_enabled_by_key(): void
    {
        // Fix the current time so date-based logic remains deterministic for the test.
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        // Create an active, enabled flag with a key and valid scheduling to simulate a live feature.
        FeatureFlag::factory()->create([
            'name'        => 'Checkout Improvements',
            'key'         => 'checkout-improvements',
            'is_active'   => true,
            'is_enabled'  => true,
            'environment' => null,
            'starts_at'   => Carbon::now()->subDay(),
            'ends_at'     => Carbon::now()->addDay(),
        ]);

        // Create a disabled flag to ensure the helper rejects inactive configurations.
        FeatureFlag::factory()->create([
            'name'        => 'Legacy Flow',
            'key'         => 'legacy-flow',
            'is_active'   => false,
            'is_enabled'  => true,
            'environment' => null,
        ]);

        // Assert the helper reports the active flag as enabled while rejecting the inactive flag.
        $this->assertTrue(FeatureFlag::isFeatureEnabled('checkout-improvements'));
        $this->assertFalse(FeatureFlag::isFeatureEnabled('legacy-flow'));

        // Clear the mocked time so other tests are not affected by the manual override.
        Carbon::setTestNow(null);
    }
}
