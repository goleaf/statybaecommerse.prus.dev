<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Brand;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserProductInteraction;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use App\Services\ReportGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReportGenerationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_analytics_report_includes_engagement_metrics(): void
    {
        // Arrange a visible product with a related variant so variant analytics can surface meaningful data points.
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product, 'product')->create([
            'views_count'     => 12,
            'sold_quantity'   => 4,
            'conversion_rate' => 33.33,
        ]);

        // Create shoppers that will generate interaction records for the analytics rollup.
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        // Seed deterministic view events so view tracking totals and unique viewer counts can be asserted.
        UserProductInteraction::factory()
            ->for($firstUser, 'user')
            ->for($product, 'product')
            ->view()
            ->create([
                'count'       => 5,
                'occurred_at' => now()->subDay(),
                'meta'        => [
                    'count'             => 5,
                    'first_interaction' => now()->subDays(3)->toDateTimeString(),
                    'last_interaction'  => now()->subDay()->toDateTimeString(),
                ],
            ]);

        UserProductInteraction::factory()
            ->for($secondUser, 'user')
            ->for($product, 'product')
            ->view()
            ->create([
                'count'       => 3,
                'occurred_at' => now()->subHours(12),
                'meta'        => [
                    'count'             => 3,
                    'first_interaction' => now()->subDays(2)->toDateTimeString(),
                    'last_interaction'  => now()->subHours(12)->toDateTimeString(),
                ],
            ]);

        // Capture cart additions so the engagement and conversion rate calculations exercise real data.
        UserProductInteraction::factory()
            ->for($firstUser, 'user')
            ->for($product, 'product')
            ->addToCart()
            ->create([
                'count'       => 2,
                'occurred_at' => now(),
                'meta'        => [
                    'count'             => 2,
                    'first_interaction' => now()->toDateTimeString(),
                    'last_interaction'  => now()->toDateTimeString(),
                ],
            ]);

        // Persist a completed order so purchase quantities flow through the analytics summary.
        $order = Order::factory()->for($firstUser, 'user')->create();
        OrderItem::factory()
            ->for($order, 'order')
            ->forProduct($product)
            ->create([
                'quantity' => 4,
                'total'    => 4 * $product->price,
            ]);

        // Wishlist entries provide another engagement metric that should appear in the report payload.
        $wishlist = UserWishlist::factory()->for($firstUser, 'user')->create();
        WishlistItem::factory()->for($wishlist, 'wishlist')->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity'   => 1,
        ]);
        WishlistItem::factory()->for($wishlist, 'wishlist')->create([
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        // Act: generate the analytics report using the enriched dataset above.
        $report = app(ReportGenerationService::class)->generateProductAnalyticsReport();

        // Assert that the summary captures the aggregate totals we expect from the seeded interactions.
        $this->assertSame(1, $report['summary']['total_products']);
        $this->assertSame(1, $report['summary']['processed_products']);
        $this->assertSame(8, $report['summary']['totals']['views']);
        $this->assertSame(2, $report['summary']['totals']['cart_additions']);
        $this->assertSame(4, $report['summary']['totals']['purchases']);
        $this->assertSame(2, $report['summary']['totals']['wishlist_additions']);
        $this->assertEqualsWithDelta(25.0, $report['summary']['average_conversion_rates']['cart_to_view'], 0.01);
        $this->assertEqualsWithDelta(50.0, $report['summary']['average_conversion_rates']['purchase_to_view'], 0.01);

        // Extract the analytics payload for the configured product and validate its nested metrics.
        $productPayload = collect($report['products'])->firstWhere('id', $product->id);
        $this->assertNotNull($productPayload);

        $this->assertSame(8, $productPayload['analytics']['view_tracking']['total_views']);
        $this->assertSame(2, $productPayload['analytics']['view_tracking']['unique_viewers']);
        $this->assertSame(2, $productPayload['analytics']['engagement']['cart_additions']);
        $this->assertSame(4, $productPayload['analytics']['engagement']['purchases']);
        $this->assertSame(1, $productPayload['analytics']['engagement']['orders']);
        $this->assertEqualsWithDelta(25.0, $productPayload['analytics']['conversion_rates']['cart_to_view_rate'], 0.01);
        $this->assertEqualsWithDelta(50.0, $productPayload['analytics']['conversion_rates']['purchase_to_view_rate'], 0.01);
        $this->assertSame(2, $productPayload['analytics']['wishlist']['wishlist_additions']);

        $this->assertSame(1, $productPayload['analytics']['variant_analytics']['total_variants']);
        $this->assertCount(1, $productPayload['analytics']['variant_analytics']['top_variants']);
        $topVariant = $productPayload['analytics']['variant_analytics']['top_variants'][0];
        $this->assertSame($variant->id, $topVariant['id']);
        $this->assertSame(12, $topVariant['views_count']);
        $this->assertSame(4, $topVariant['sold_quantity']);
        $this->assertEqualsWithDelta(33.33, $topVariant['conversion_rate'], 0.01);
    }

    public function test_product_analytics_report_respects_brand_filter(): void
    {
        // Provision two brands so we can scope the report to a single catalogue line.
        $focusedBrand = Brand::factory()->create();
        $otherBrand = Brand::factory()->create();

        // The target product includes a view interaction to guarantee it is present when filtered.
        $focusedProduct = Product::factory()->for($focusedBrand, 'brand')->create();
        UserProductInteraction::factory()
            ->for(User::factory(), 'user')
            ->for($focusedProduct, 'product')
            ->view()
            ->create([
                'count'       => 2,
                'occurred_at' => now(),
                'meta'        => [
                    'count'             => 2,
                    'first_interaction' => now()->subDay()->toDateTimeString(),
                    'last_interaction'  => now()->toDateTimeString(),
                ],
            ]);

        // Create a second product under a different brand that should be excluded from the filtered results.
        Product::factory()->for($otherBrand, 'brand')->create();

        // Act: restrict the report to the focused brand identifier.
        $report = app(ReportGenerationService::class)->generateProductAnalyticsReport([
            'brand_id' => $focusedBrand->id,
        ]);

        // Assert: only the focused product appears in the payload and the summary reflects that single record.
        $this->assertSame(1, $report['summary']['total_products']);
        $this->assertSame([$focusedProduct->id], array_column($report['products'], 'id'));
        $this->assertSame(2, $report['summary']['totals']['views']);
    }
}
