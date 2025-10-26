<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Partner;
use App\Models\PartnerTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PartnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_has_expected_fillable_and_casts(): void
    {
        // Instantiate the model without persisting data so we can inspect configuration arrays.
        $model = new Partner;

        // Verify the fillable whitelist protects against mass-assignment of unintended attributes.
        self::assertSame([
            'name',
            'code',
            'tier_id',
            'contact_email',
            'contact_phone',
            'is_enabled',
            'discount_rate',
            'commission_rate',
            'metadata',
        ], $model->getFillable());

        // Confirm the casts definition automatically coerces booleans, decimals, and metadata arrays.
        $casts = $model->getCasts();
        self::assertArrayHasKey('is_enabled', $casts);
        self::assertSame('boolean', $casts['is_enabled']);
        self::assertArrayHasKey('discount_rate', $casts);
        self::assertSame('decimal:4', $casts['discount_rate']);
        self::assertArrayHasKey('commission_rate', $casts);
        self::assertSame('decimal:4', $casts['commission_rate']);
        self::assertArrayHasKey('metadata', $casts);
        self::assertSame('array', $casts['metadata']);
    }

    public function test_tier_relationship_returns_associated_model(): void
    {
        // Create a tier first so we can ensure the belongsTo relation resolves the correct instance.
        $tier = PartnerTier::factory()->create();

        // Persist a partner that references the previously created tier.
        $partner = Partner::factory()->for($tier, 'tier')->create();

        // Refresh the partner from the database to hydrate the relationship when accessed.
        $partner = $partner->fresh();
        self::assertInstanceOf(Partner::class, $partner);
        $relatedTier = $partner->tier()->first();
        self::assertInstanceOf(PartnerTier::class, $relatedTier);

        // Assert the tier relation returns the same model instance that was associated.
        self::assertTrue($tier->is($relatedTier));
    }

    public function test_query_scopes_filter_and_order_partners(): void
    {
        // Create two tiers so we can validate the byTier scope alongside enabled filtering.
        $primaryTier = PartnerTier::factory()->create();
        $secondaryTier = PartnerTier::factory()->create();

        // Seed partners with varying names, tiers, and enabled flags to exercise each scope.
        $alpha = Partner::factory()->for($primaryTier, 'tier')->create(['name' => 'Alpha Supplies', 'is_enabled' => true]);
        $gamma = Partner::factory()->for($primaryTier, 'tier')->create(['name' => 'Gamma Trading', 'is_enabled' => false]);
        $beta = Partner::factory()->for($secondaryTier, 'tier')->create(['name' => 'Beta Logistics', 'is_enabled' => true]);

        // Apply the chained scopes to retrieve enabled partners for the primary tier ordered by their names.
        /** @var int $primaryTierId */
        $primaryTierId = $primaryTier->getKey();

        $results = Partner::query()
            ->byTier($primaryTierId)
            ->enabled()
            ->orderedByName()
            ->pluck('id')
            ->all();

        // Only the enabled partner belonging to the requested tier should appear in alphabetical order.
        self::assertSame([$alpha->getKey()], $results);

        // When ordering all partners by name we expect them in alphabetical order irrespective of tier.
        $orderedNames = Partner::query()->orderedByName()->pluck('name')->all();
        self::assertSame([
            'Alpha Supplies',
            'Beta Logistics',
        ], $orderedNames);
    }

    public function test_effective_discount_rate_falls_back_to_tier_value(): void
    {
        // Create a tier with a known discount so we can assert the accessor fallback behaviour.
        $tier = PartnerTier::factory()->create(['discount_rate' => 0.0750]);

        // Persist two partners: one with an explicit discount and another relying on the tier value.
        $explicit = Partner::factory()->for($tier, 'tier')->create(['discount_rate' => 0.0500]);
        $inherited = Partner::factory()->for($tier, 'tier')->create(['discount_rate' => 0.0000]);

        // The accessor should return the partner-specific discount when defined.
        $explicit->refresh();
        self::assertSame(0.0500, $explicit->getAttribute('effective_discount_rate'));

        // When the partner discount is null it should fall back to the tier's configured discount even without eager loading.
        $inherited->refresh();
        $inherited->setAttribute('discount_rate', null);
        $inherited->unsetRelation('tier');
        self::assertSame(0.0750, $inherited->getAttribute('effective_discount_rate'));
    }

    public function test_effective_commission_rate_falls_back_to_tier_value(): void
    {
        // Create a tier with a known commission so the accessor fallback can be asserted precisely.
        $tier = PartnerTier::factory()->create(['commission_rate' => 0.0225]);

        // Persist partners covering both explicit commission overrides and inherited tier defaults.
        $explicit = Partner::factory()->for($tier, 'tier')->create(['commission_rate' => 0.0175]);
        $inherited = Partner::factory()->for($tier, 'tier')->create(['commission_rate' => 0.0000]);

        // Partner-specific overrides should be returned unchanged.
        $explicit->refresh();
        self::assertSame(0.0175, $explicit->getAttribute('effective_commission_rate'));

        // Partners without an override should inherit the tier commission, even when the relation was not eager loaded.
        $inherited->refresh();
        $inherited->setAttribute('commission_rate', null);
        $inherited->unsetRelation('tier');
        self::assertSame(0.0225, $inherited->getAttribute('effective_commission_rate'));
    }
}
