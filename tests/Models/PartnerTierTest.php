<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Partner;
use App\Models\PartnerTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PartnerTierTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_tier_has_expected_configuration(): void
    {
        // Instantiate the model so we can inspect configuration without touching the database.
        $model = new PartnerTier;

        // Validate fillable columns to guard against mass-assignment issues.
        self::assertSame([
            'name',
            'code',
            'discount_rate',
            'commission_rate',
            'minimum_order_value',
            'is_enabled',
            'benefits',
        ], $model->getFillable());

        // Ensure casting rules format data correctly when retrieved from the database.
        $casts = $model->getCasts();
        self::assertArrayHasKey('discount_rate', $casts);
        self::assertSame('decimal:4', $casts['discount_rate']);
        self::assertArrayHasKey('commission_rate', $casts);
        self::assertSame('decimal:4', $casts['commission_rate']);
        self::assertArrayHasKey('minimum_order_value', $casts);
        self::assertSame('decimal:2', $casts['minimum_order_value']);
        self::assertArrayHasKey('is_enabled', $casts);
        self::assertSame('boolean', $casts['is_enabled']);
        self::assertArrayHasKey('benefits', $casts);
        self::assertSame('array', $casts['benefits']);
    }

    public function test_partners_relationship_returns_related_partners(): void
    {
        // Create a tier and an associated partner to exercise the relationship definition.
        $tier = PartnerTier::factory()->create();
        $partner = Partner::factory()->for($tier, 'tier')->create();

        // Reload the relationship to verify the instance types and linkage.
        $loadedPartner = $tier->fresh()->partners->first();
        self::assertInstanceOf(Partner::class, $loadedPartner);
        self::assertTrue($partner->is($loadedPartner));
    }

    public function test_enabled_scope_filters_disabled_tiers(): void
    {
        // Prepare enabled and disabled records so we can assert the scope filtering behaviour.
        $enabledTier = PartnerTier::factory()->enabled()->create();
        $disabledTier = PartnerTier::factory()->disabled()->create();

        // Execute the scope and confirm only enabled tiers are returned.
        $results = PartnerTier::query()->enabled()->get();
        self::assertTrue($results->contains($enabledTier));
        self::assertFalse($results->contains($disabledTier));
    }

    public function test_by_discount_rate_scope_filters_by_value(): void
    {
        // Create partner tiers with deterministic discount rates for precise filtering.
        $matchingTier = PartnerTier::factory()->create(['discount_rate' => 0.0500]);
        $nonMatchingTier = PartnerTier::factory()->create(['discount_rate' => 0.0700]);

        // Run the scope to ensure it matches the expected record while excluding others.
        $results = PartnerTier::query()->byDiscountRate(0.0500)->get();
        self::assertTrue($results->contains($matchingTier));
        self::assertFalse($results->contains($nonMatchingTier));
    }

    public function test_ordered_by_name_scope_sorts_alphabetically(): void
    {
        // Create tiers out of order so we can verify the alphabetical sort order.
        $gamma = PartnerTier::factory()->create(['name' => 'Gamma', 'code' => 'gamma-001']);
        $alpha = PartnerTier::factory()->create(['name' => 'Alpha', 'code' => 'alpha-001']);
        $beta = PartnerTier::factory()->create(['name' => 'Beta', 'code' => 'beta-001']);

        // Collect IDs in the order returned by the scope and compare with the expected sequence.
        $orderedIds = PartnerTier::query()->orderedByName()->pluck('id')->all();
        self::assertSame([
            $alpha->getKey(),
            $beta->getKey(),
            $gamma->getKey(),
        ], $orderedIds);
    }
}
