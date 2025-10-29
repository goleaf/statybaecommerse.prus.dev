<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ProductVariant;
use App\Models\VariantAnalytics;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class VariantAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Reset any mocked "now" instances so other tests retain fresh timestamps.
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_variant_analytics_belongs_to_variant(): void
    {
        // Arrange a concrete variant so the relationship can be asserted explicitly.
        $variant = ProductVariant::factory()->create();
        $analytics = $this->createAnalytics([], $variant);

        // Act & Assert: the relation should resolve to the original variant model.
        $this->assertInstanceOf(ProductVariant::class, $analytics->variant);
        $this->assertSame($variant->id, $analytics->variant->id);
    }

    #[DataProvider('rateExpectations')]
    public function test_rate_accessors_calculate_expected_percentages(
        array $overrides,
        string $accessor,
        float $expected
    ): void {
        // Arrange a deterministic analytics row for each accessor scenario.
        $analytics = $this->createAnalytics($overrides);

        // Assert the derived value matches the computed expectation (cast to float for consistency).
        $this->assertSame($expected, (float) $analytics->{$accessor});
    }

    #[DataProvider('rateZeroExpectations')]
    public function test_rate_accessors_fall_back_to_zero_when_denominator_missing(
        array $overrides,
        string $accessor
    ): void {
        // Arrange a row where the denominator is zero to trigger the guard clause.
        $analytics = $this->createAnalytics($overrides);

        // Assert the accessor returns a neutral value so dashboards avoid divide-by-zero warnings.
        $this->assertSame(0.0, (float) $analytics->{$accessor});
    }

    public function test_scope_in_date_range_filters_correctly(): void
    {
        // Freeze time to keep the relative date calculations deterministic.
        Carbon::setTestNow('2025-01-20 12:00:00');

        $variant = ProductVariant::factory()->create();
        $insideWindow = $this->createAnalyticsForDate($variant, Carbon::now()->subDays(5));
        $outsideWindow = $this->createAnalyticsForDate($variant, Carbon::now()->subDays(15));

        // Act: request rows within the previous ten days.
        $result = VariantAnalytics::inDateRange(
            Carbon::now()->subDays(10)->toDateString(),
            Carbon::now()->toDateString()
        )->get();

        // Assert: only the analytics inside the window remain in the collection.
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($insideWindow));
        $this->assertFalse($result->contains($outsideWindow));
    }

    public function test_scope_recent_defaults_to_daily_rows(): void
    {
        // Freeze time so "recent" checks behave predictably across database engines.
        Carbon::setTestNow('2025-02-10 08:00:00');

        $variant = ProductVariant::factory()->create();
        $recent = $this->createAnalyticsForDate($variant, Carbon::now()->subDays(5));
        $historical = $this->createAnalyticsForDate($variant, Carbon::now()->subDays(40));

        // Act: pull records from the last 30 days (the default window).
        $result = VariantAnalytics::recent()->get();

        // Assert only the recent item is returned.
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($recent));
        $this->assertFalse($result->contains($historical));
    }

    public function test_scope_top_performing_prioritises_conversion_and_revenue(): void
    {
        // Arrange two analytics rows with contrasting conversion and revenue figures.
        $variant = ProductVariant::factory()->create();
        $lowPerformer = $this->createAnalytics([
            'conversion_rate' => 1.0,
            'revenue' => 100.0,
        ], $variant);
        $highPerformer = $this->createAnalytics([
            'conversion_rate' => 10.0,
            'revenue' => 1000.0,
        ], $variant);

        // Act: request the single top performer.
        $result = VariantAnalytics::topPerforming(1)->get();

        // Assert the best performing row is surfaced and the weaker entry is excluded.
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($highPerformer));
        $this->assertFalse($result->contains($lowPerformer));
    }

    public function test_scope_by_metric_honours_direction_and_metric_name(): void
    {
        // Arrange analytics rows that differ only by revenue so ordering can be asserted precisely.
        $variant = ProductVariant::factory()->create();
        $lowRevenue = $this->createAnalytics(['revenue' => 100.00], $variant);
        $highRevenue = $this->createAnalytics(['revenue' => 1000.00], $variant);

        // Act: order descending by revenue.
        $result = VariantAnalytics::byMetric('revenue', 'desc')->get();

        // Assert the higher revenue entry appears first and the lower revenue entry appears last.
        $this->assertCount(2, $result);
        $this->assertSame($highRevenue->id, $result->first()->id);
        $this->assertSame($lowRevenue->id, $result->last()->id);
    }

    public function test_for_granularity_scope_matches_bucket_prefix(): void
    {
        // Arrange rows tagged as daily and weekly to verify the prefix filter.
        $daily = $this->createAnalytics([
            'date' => '2025-01-01',
            'date_bucket' => 'daily:2025-01-01',
        ]);
        $weekly = $this->createAnalytics([
            'date' => '2024-12-30',
            'date_bucket' => 'weekly:2024-12-30',
        ]);

        // Act & Assert: only the weekly bucket should survive when filtering by the weekly prefix.
        $weeklyResults = VariantAnalytics::query()->forGranularity(VariantAnalytics::BUCKET_WEEKLY)->get();
        $this->assertTrue($weeklyResults->contains($weekly));
        $this->assertFalse($weeklyResults->contains($daily));

        // And conversely ensure the daily scope excludes the weekly row.
        $dailyResults = VariantAnalytics::query()->daily()->get();
        $this->assertTrue($dailyResults->contains($daily));
        $this->assertFalse($dailyResults->contains($weekly));
    }

    public function test_record_analytics_creates_daily_row_with_normalised_payload(): void
    {
        // Freeze time to make created/updated timestamps deterministic when asserting the payload.
        Carbon::setTestNow('2025-03-01 09:15:00');

        $variant = ProductVariant::factory()->create();
        $payload = [
            'views' => 100,
            'clicks' => 50,
            'revenue' => 500.00,
        ];

        // Act: record analytics without providing a product identifier to exercise the resolver path.
        $analytics = VariantAnalytics::recordAnalytics($variant->id, '2025-02-28', $payload);

        // Assert the row inherits the product, resolves the bucket, and persists numeric metrics verbatim.
        $this->assertSame($variant->product_id, $analytics->product_id);
        $this->assertSame($variant->id, $analytics->variant_id);
        $this->assertSame('2025-02-28', $analytics->date->toDateString());
        $this->assertSame('daily:2025-02-28', $analytics->date_bucket);
        $this->assertSame(100, $analytics->views);
        $this->assertSame(50, $analytics->clicks);
        $this->assertSame('500.0000', (string) $analytics->revenue);
    }

    public function test_record_analytics_updates_existing_row_and_recalculates_metrics(): void
    {
        // Freeze the clock to keep weekly/daily boundaries and timestamps stable.
        Carbon::setTestNow('2025-04-10 10:30:00');

        $variant = ProductVariant::factory()->create();

        $existing = VariantAnalytics::factory()
            ->withVariant($variant)
            ->forDate('2025-04-09')
            ->create([
                'views' => 50,
                'purchases' => 5,
                'conversion_rate' => 10.0,
            ]);

        // Act: add fresh metrics for the same bucket so the row is updated rather than recreated.
        $analytics = VariantAnalytics::recordAnalytics(
            $variant->id,
            '2025-04-09',
            [
                'views' => 25,
                'purchases' => 2,
            ]
        );

        // Assert metrics are incremented and the conversion rate is recalculated from the updated counts.
        $this->assertSame($existing->id, $analytics->id);
        $this->assertSame(75, $analytics->views);
        $this->assertSame(7, $analytics->purchases);
        $this->assertEqualsWithDelta(9.3333, (float) $analytics->conversion_rate, 0.0001);
        $this->assertDatabaseCount('variant_analytics', 1);
    }

    public function test_record_analytics_tracks_weekly_buckets(): void
    {
        // Freeze time so the start-of-week helper produces a stable bucket value.
        Carbon::setTestNow('2025-05-14 16:00:00');

        $variant = ProductVariant::factory()->create();
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();

        // Act: record two updates against the same weekly bucket.
        VariantAnalytics::recordAnalytics(
            $variant->id,
            Carbon::now()->toDateString(),
            ['views' => 10],
            VariantAnalytics::BUCKET_WEEKLY
        );

        $analytics = VariantAnalytics::recordAnalytics(
            $variant->id,
            Carbon::now()->toDateString(),
            ['views' => 5],
            VariantAnalytics::BUCKET_WEEKLY
        );

        // Assert a single row exists with aggregated views and the expected bucket label.
        $this->assertSame('weekly:' . $startOfWeek, $analytics->date_bucket);
        $this->assertSame(15, $analytics->views);
        $this->assertDatabaseCount('variant_analytics', 1);
    }

    public function test_record_analytics_replaces_conversion_rate_when_explicit_value_provided(): void
    {
        // Arrange an existing row to prove the replacement logic wins over the incremental path.
        $variant = ProductVariant::factory()->create();

        VariantAnalytics::factory()
            ->withVariant($variant)
            ->forDate('2025-06-01')
            ->create([
                'views' => 200,
                'purchases' => 10,
                'conversion_rate' => 5.0,
            ]);

        // Act: supply an explicit conversion rate so the updater replaces the stored value directly.
        $analytics = VariantAnalytics::recordAnalytics(
            $variant->id,
            '2025-06-01',
            [
                'views' => 0,
                'conversion_rate' => 12.5,
            ]
        );

        // Assert the conversion rate reflects the provided value rather than a recalculated figure.
        $this->assertSame('12.5000', (string) $analytics->conversion_rate);
    }

    public function test_record_analytics_rejects_unknown_granularity(): void
    {
        // Expect an exception when callers supply an unsupported bucket identifier.
        $variant = ProductVariant::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported analytics granularity');

        VariantAnalytics::recordAnalytics($variant->id, '2025-07-01', [], 'monthly');
    }

    public function test_increment_metric_updates_correctly(): void
    {
        // Arrange a row with a baseline number of views.
        $analytics = $this->createAnalytics(['views' => 100]);

        // Act: increment the chosen metric by a custom amount.
        $result = $analytics->incrementMetric('views', 50);

        // Assert the increment succeeded and the persisted value was updated.
        $this->assertTrue($result);
        $this->assertSame(150, $analytics->fresh()->views);
    }

    public function test_update_conversion_rate_calculates_correctly(): void
    {
        // Arrange deterministic counts so the conversion calculation is known.
        $analytics = $this->createAnalytics([
            'views' => 1000,
            'purchases' => 50,
            'conversion_rate' => 0,
        ]);

        // Act
        $result = $analytics->updateConversionRate();

        // Assert the stored value reflects the derived percentage.
        $this->assertTrue($result);
        $this->assertSame('5.0000', (string) $analytics->fresh()->conversion_rate);
    }

    public function test_update_conversion_rate_handles_zero_views(): void
    {
        // Arrange: zero views should always reset the conversion rate to zero.
        $analytics = $this->createAnalytics([
            'views' => 0,
            'purchases' => 50,
            'conversion_rate' => 10,
        ]);

        // Act & Assert
        $this->assertTrue($analytics->updateConversionRate());
        $this->assertSame('0.0000', (string) $analytics->fresh()->conversion_rate);
    }

    public function test_fillable_attributes_are_correct(): void
    {
        // Assert the fillable definition matches the documented contract for mass assignment.
        $this->assertSame([
            'product_id',
            'variant_id',
            'date',
            'date_bucket',
            'views',
            'clicks',
            'add_to_cart',
            'purchases',
            'revenue',
            'conversion_rate',
        ], (new VariantAnalytics())->getFillable());
    }

    public function test_casts_are_correct(): void
    {
        // Arrange explicit string payloads to ensure casts normalise the data types.
        $analytics = VariantAnalytics::factory()->create([
            'views' => '100',
            'clicks' => '50',
            'revenue' => '500.1234',
            'conversion_rate' => '10.5678',
        ]);

        // Assert primitive casts and the date mutator behave as expected.
        $this->assertIsInt($analytics->product_id);
        $this->assertIsInt($analytics->views);
        $this->assertIsInt($analytics->clicks);
        $this->assertIsString($analytics->date_bucket);
        $this->assertIsString($analytics->revenue); // Decimal casts return string representations.
        $this->assertIsString($analytics->conversion_rate);
        $this->assertInstanceOf(DateTime::class, $analytics->date);
    }

    /**
     * Provide expected metric inputs and outputs for the accessor tests.
     */
    public static function rateExpectations(): iterable
    {
        yield 'click-through rate' => [
            ['views' => 1000, 'clicks' => 100],
            'click_through_rate',
            10.0,
        ];

        yield 'add-to-cart rate' => [
            ['clicks' => 200, 'add_to_cart' => 50],
            'add_to_cart_rate',
            25.0,
        ];

        yield 'purchase rate' => [
            ['add_to_cart' => 100, 'purchases' => 20],
            'purchase_rate',
            20.0,
        ];

        yield 'average revenue per purchase' => [
            ['revenue' => 1000.0, 'purchases' => 10],
            'average_revenue_per_purchase',
            100.0,
        ];
    }

    /**
     * Provide scenarios where rate accessors should short-circuit to zero.
     */
    public static function rateZeroExpectations(): iterable
    {
        yield 'click-through rate guarded by views' => [
            ['views' => 0, 'clicks' => 10],
            'click_through_rate',
        ];

        yield 'add-to-cart rate guarded by clicks' => [
            ['clicks' => 0, 'add_to_cart' => 10],
            'add_to_cart_rate',
        ];

        yield 'purchase rate guarded by add-to-cart' => [
            ['add_to_cart' => 0, 'purchases' => 10],
            'purchase_rate',
        ];

        yield 'average revenue per purchase guarded by purchases' => [
            ['purchases' => 0, 'revenue' => 1000],
            'average_revenue_per_purchase',
        ];
    }

    /**
     * Create a deterministic analytics row with sensible defaults for derived metric assertions.
     */
    private function createAnalytics(array $overrides = [], ?ProductVariant $variant = null): VariantAnalytics
    {
        $variant ??= ProductVariant::factory()->create();

        $defaults = [
            'views' => 0,
            'clicks' => 0,
            'add_to_cart' => 0,
            'purchases' => 0,
            'revenue' => 0.0,
            'conversion_rate' => 0.0,
        ];

        return VariantAnalytics::factory()
            ->withVariant($variant)
            ->create(array_merge($defaults, $overrides));
    }

    /**
     * Helper that pins the analytics date to a specific day so scope queries stay precise.
     */
    private function createAnalyticsForDate(
        ProductVariant $variant,
        Carbon|string $date,
        array $overrides = []
    ): VariantAnalytics {
        $normalizedDate = Carbon::parse($date)->toDateString();

        return VariantAnalytics::factory()
            ->withVariant($variant)
            ->forDate($normalizedDate)
            ->create(array_merge([
                'views' => 0,
                'clicks' => 0,
                'add_to_cart' => 0,
                'purchases' => 0,
                'revenue' => 0.0,
                'conversion_rate' => 0.0,
            ], $overrides));
    }
}
