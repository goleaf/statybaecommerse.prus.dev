<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Product;
use App\Models\StockReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_configuration_exposes_expected_fillable_casts_and_appends(): void
    {
        // Instantiate the model to interrogate its configuration without persisting it.
        $model = new Product;

        // The fillable definition should explicitly list key catalog attributes to guard against mass-assignment regressions.
        self::assertContains('name', $model->getFillable());
        self::assertContains('slug', $model->getFillable());
        self::assertContains('price', $model->getFillable());

        // Casting rules should ensure numeric precision and boolean flags are hydrated consistently.
        self::assertSame('decimal:2', $model->getCasts()['price'] ?? null);
        self::assertSame('boolean', $model->getCasts()['is_visible'] ?? null);

        // Appended accessors should expose computed inventory metadata to API consumers by default.
        self::assertContains('available_quantity', $model->getAppends());
        self::assertContains('stock_status', $model->getAppends());
    }

    public function test_scope_ordered_by_name_sorts_records_using_alpha_order(): void
    {
        // Seed three products with out-of-order names while keeping them publishable for global scopes.
        $gamma = Product::factory()->create([
            'name' => 'Gamma Anchor',
            'slug' => 'gamma-anchor',
        ]);
        $alpha = Product::factory()->create([
            'name' => 'Alpha Bolt',
            'slug' => 'alpha-bolt',
        ]);
        $beta = Product::factory()->create([
            'name' => 'Beta Clamp',
            'slug' => 'beta-clamp',
        ]);

        // Invoke the scoped query to validate deterministic alphabetical ordering.
        $orderedIds = Product::query()
            ->withoutGlobalScopes()
            ->orderedByName()
            ->pluck('id')
            ->all();

        self::assertSame([
            $alpha->getKey(),
            $beta->getKey(),
            $gamma->getKey(),
        ], $orderedIds);
    }

    public function test_translatable_attributes_are_serialised_when_arrays_are_assigned(): void
    {
        // Work with a fresh model instance so assignments happen in-memory.
        $product = new Product;

        // Provide multilingual payloads that should be normalised into JSON storage.
        $product->setAttribute('name', [
            'en' => 'Safety Helmet',
            'lt' => 'Apsauginis šalmas',
        ]);

        // Extract the raw attribute to confirm it was serialised and retains the translations.
        $raw = $product->getAttributes()['name'] ?? null;
        self::assertIsString($raw);
        self::assertJson($raw);
        self::assertSame([
            'en' => 'Safety Helmet',
            'lt' => 'Apsauginis šalmas',
        ], json_decode($raw, true, 512, JSON_THROW_ON_ERROR));
    }

    public function test_available_quantity_excludes_released_or_expired_reservations(): void
    {
        // Freeze time to guarantee repeatable reservation window comparisons.
        $now = Carbon::parse('2024-01-15 12:00:00');
        Carbon::setTestNow($now);

        try {
            // Create a stock-managed product to exercise the inventory helpers.
            $product = Product::factory()->create([
                'manage_stock'   => true,
                'stock_quantity' => 10,
                'slug'           => 'stocked-hammer',
                'name'           => 'Stocked Hammer',
            ]);

            // Create an active reservation that should deduct from the available quantity.
            StockReservation::query()->create([
                'product_id'  => $product->getKey(),
                'quantity'    => 3,
                'status'      => StockReservation::STATUS_RESERVED,
                'reserved_at' => $now->copy()->subMinutes(5),
                'expires_at'  => $now->copy()->addHour(),
            ]);

            // Add a released reservation that should be ignored during availability checks.
            StockReservation::query()->create([
                'product_id'  => $product->getKey(),
                'quantity'    => 2,
                'status'      => StockReservation::STATUS_RELEASED,
                'reserved_at' => $now->copy()->subHour(),
                'released_at' => $now->copy()->subMinutes(10),
            ]);

            // Add an expired reservation to ensure past-due holds are not counted.
            StockReservation::query()->create([
                'product_id'  => $product->getKey(),
                'quantity'    => 1,
                'status'      => StockReservation::STATUS_RESERVED,
                'reserved_at' => $now->copy()->subHours(2),
                'expires_at'  => $now->copy()->subMinutes(1),
            ]);

            // Refresh the model to include the newly persisted relationships and evaluate the helper.
            $product->refresh();
            self::assertSame(7, $product->availableQuantity());
            self::assertTrue($product->isInStock());
        } finally {
            // Always clear the mocked clock to avoid leaking state between tests.
            Carbon::setTestNow();
        }
    }
}
