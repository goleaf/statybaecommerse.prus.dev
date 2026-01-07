<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Partner;
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

    public function test_query_scopes_filter_and_order_partners(): void
    {
        // Seed partners with varying names and enabled flags to exercise each scope.
        $alpha = Partner::factory()->create(['name' => 'Alpha Supplies', 'is_enabled' => true]);
        $gamma = Partner::factory()->create(['name' => 'Gamma Trading', 'is_enabled' => false]);
        $beta = Partner::factory()->create(['name' => 'Beta Logistics', 'is_enabled' => true]);

        // Apply the scopes to retrieve enabled partners ordered by their names.
        $results = Partner::query()
            ->enabled()
            ->orderedByName()
            ->pluck('name')
            ->all();

        // Only the enabled partners should appear in alphabetical order.
        self::assertSame([
            'Alpha Supplies',
            'Beta Logistics',
        ], $results);

        // When ordering all partners by name we expect them in alphabetical order.
        $orderedNames = Partner::query()->orderedByName()->pluck('name')->all();
        self::assertSame([
            'Alpha Supplies',
            'Beta Logistics',
        ], $orderedNames);
    }

    public function test_partner_discount_and_commission_rates(): void
    {
        // Create partners with specific discount and commission rates.
        $partner = Partner::factory()->create([
            'discount_rate'   => 0.0500,
            'commission_rate' => 0.0175,
        ]);

        // Verify the rates are stored and retrieved correctly.
        $partner->refresh();
        self::assertSame('0.0500', (string) $partner->getAttribute('discount_rate'));
        self::assertSame('0.0175', (string) $partner->getAttribute('commission_rate'));
    }
}
