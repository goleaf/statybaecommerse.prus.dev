<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\ProductHistoryController;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View as IlluminateView;
use Tests\TestCase;

final class ProductHistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_show_renders_product_history_with_default_filters(): void
    {
        $product = Product::factory()->create();
        $otherProduct = Product::factory()->create();

        // Seed a variety of history entries so aggregate counts can be asserted reliably.
        $latestHistory = ProductHistory::factory()->for($product)->updated()->create([
            'created_at' => now(),
        ]);
        ProductHistory::factory()->for($product)->priceChanged()->create([
            'created_at' => now()->subDays(2),
        ]);
        ProductHistory::factory()->for($product)->stockUpdated()->create([
            'created_at' => now()->subDays(3),
        ]);
        ProductHistory::factory()->for($otherProduct)->create();

        $request = Request::create('/products/' . $product->slug . '/history', 'GET');

        $response = app(ProductHistoryController::class)->show($request, $product);

        self::assertInstanceOf(IlluminateView::class, $response);
        self::assertSame('livewire.pages.product-history', $response->name());

        $data = $response->getData();

        self::assertSame(3, $data['totalChanges']);
        self::assertSame(1, $data['priceChanges']);
        self::assertSame(1, $data['stockUpdates']);
        self::assertNotNull($data['lastChange']);
        self::assertTrue($data['lastChange']->is($latestHistory));
        self::assertInstanceOf(LengthAwarePaginator::class, $data['history']);
        self::assertSame(3, $data['history']->total());
        self::assertTrue($data['history']->first()?->is($latestHistory));
    }

    public function test_show_applies_action_and_date_filters_from_query(): void
    {
        Carbon::setTestNow('2025-01-15 12:00:00');

        $product = Product::factory()->create();

        // Create history entries that the filters should include/exclude.
        $expectedHistory = ProductHistory::factory()->for($product)->priceChanged()->create([
            'created_at' => now()->subDays(3),
        ]);
        ProductHistory::factory()->for($product)->priceChanged()->create([
            'created_at' => now()->subDays(40),
        ]);
        ProductHistory::factory()->for($product)->stockUpdated()->create([
            'created_at' => now()->subDays(2),
        ]);

        $request = Request::create(
            '/products/' . $product->slug . '/history',
            'GET',
            ['action' => 'price_changed', 'date' => '7', 'per_page' => '1']
        );

        $response = app(ProductHistoryController::class)->show($request, $product);

        self::assertInstanceOf(IlluminateView::class, $response);

        $data = $response->getData();

        self::assertSame('price_changed', $data['actionFilter']);
        self::assertSame('7', $data['dateFilter']);
        // Invalid per-page values should gracefully fall back to the default size.
        self::assertSame(20, $data['perPage']);
        self::assertInstanceOf(LengthAwarePaginator::class, $data['history']);
        self::assertSame(1, $data['history']->total());
        self::assertTrue($data['history']->first()?->is($expectedHistory));

        Carbon::setTestNow();
    }
}
