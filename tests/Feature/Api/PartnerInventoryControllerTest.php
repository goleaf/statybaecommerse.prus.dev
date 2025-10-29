<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\ApiKey;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PartnerInventoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $headerName;

    private string $plainTextKey;

    protected function setUp(): void
    {
        parent::setUp();

        // Provision an API key that holds the inventory scope so partner requests are authenticated.
        $credentials = ApiKey::generateCredentials();

        ApiKey::factory()->create([
            'key'         => $credentials['hashed'],
            'permissions' => ['inventory.read'],
        ]);

        $configuredHeader = config('services.partner_api.header', 'X-Partner-Key');
        $this->headerName = is_string($configuredHeader) ? $configuredHeader : 'X-Partner-Key';
        $this->plainTextKey = $credentials['plain_text'];
    }

    public function test_inventory_endpoint_returns_structured_payload(): void
    {
        // Create a representative set of products to exercise each inventory category.
        $inStock = Product::factory()->create([
            'name'                => 'Partner Drill',
            'sku'                 => 'PARTNER-DRILL',
            'manage_stock'        => true,
            'stock_quantity'      => 18,
            'low_stock_threshold' => 5,
        ]);

        $lowStock = Product::factory()->create([
            'name'                => 'Partner Saw',
            'sku'                 => 'PARTNER-SAW',
            'manage_stock'        => true,
            'stock_quantity'      => 3,
            'low_stock_threshold' => 5,
        ]);

        $outOfStock = Product::factory()->create([
            'name'                => 'Partner Grinder',
            'sku'                 => 'PARTNER-GRINDER',
            'manage_stock'        => true,
            'stock_quantity'      => 0,
            'low_stock_threshold' => 2,
        ]);

        Product::factory()->create([
            'name'         => 'Partner Gloves',
            'sku'          => 'PARTNER-GLOVES',
            'manage_stock' => false,
        ]);

        $response = $this
            ->withHeader($this->headerName, $this->plainTextKey)
            ->getJson(route('api.partner.inventory.index', ['limit' => 5]));

        $response->assertOk();

        // Confirm the summary buckets capture the expected distribution of stock states.
        $response->assertJsonPath('data.inventory.summary.total_products', 4);
        $response->assertJsonPath('data.inventory.summary.tracked_products', 3);
        $response->assertJsonPath('data.inventory.summary.in_stock', 1);
        $response->assertJsonPath('data.inventory.summary.low_stock', 1);
        $response->assertJsonPath('data.inventory.summary.out_of_stock', 1);
        $response->assertJsonPath('data.inventory.summary.not_tracked', 1);

        // Validate the low stock payload mirrors the product attributes so integrators can react accordingly.
        $lowStockPayload = $response->json('data.inventory.low_stock.0');
        $this->assertIsArray($lowStockPayload);
        $this->assertSame($lowStock->getKey(), $lowStockPayload['id']);
        $this->assertSame('PARTNER-SAW', $lowStockPayload['sku']);
        $this->assertSame(3, $lowStockPayload['inventory']['stock_quantity']);
        $this->assertTrue($lowStockPayload['inventory']['is_low_stock']);

        // Out-of-stock entries should flag the `is_out_of_stock` indicator to trigger downstream alerts.
        $outOfStockPayload = $response->json('data.inventory.out_of_stock.0');
        $this->assertIsArray($outOfStockPayload);
        $this->assertSame($outOfStock->getKey(), $outOfStockPayload['id']);
        $this->assertTrue($outOfStockPayload['inventory']['is_out_of_stock']);

        // Ensure pagination metadata is still provided for legacy partners expecting the keys to exist.
        $response->assertJsonPath('meta.pagination.per_page', 5);
        $response->assertJsonPath('meta.filters.limit', 5);
        $scopes = $response->json('meta.scopes');
        $scopes = is_array($scopes) ? $scopes : [];
        $this->assertContains('inventory.read', $scopes);
    }

    public function test_inventory_endpoint_supports_incremental_filters(): void
    {
        // Seed a tracked product and backdate it so the initial filtered request returns an empty set.
        $product = Product::factory()->create([
            'name'                => 'Filter Saw',
            'sku'                 => 'FILTER-SAW',
            'manage_stock'        => true,
            'stock_quantity'      => 2,
            'low_stock_threshold' => 5,
        ]);
        $product->forceFill(['updated_at' => CarbonImmutable::now()->subDays(2)])->save();

        $since = CarbonImmutable::now()->subDay();

        $firstResponse = $this
            ->withHeader($this->headerName, $this->plainTextKey)
            ->getJson(route('api.partner.inventory.index', [
                'sku'           => 'FILTER-SAW',
                'updated_since' => $since->toAtomString(),
            ]));

        $firstResponse->assertOk();
        $firstResponse->assertJsonPath('data.inventory.summary.total_products', 0);
        $firstResponse->assertJsonPath('data.inventory.summary.low_stock', 0);
        $this->assertSame($since->toAtomString(), $firstResponse->json('meta.filters.updated_since'));

        // Move the product inside the filter window to confirm the response immediately reflects the new state.
        $product->forceFill(['updated_at' => CarbonImmutable::now()->subHours(1)])->save();

        $secondResponse = $this
            ->withHeader($this->headerName, $this->plainTextKey)
            ->getJson(route('api.partner.inventory.index', [
                'sku'           => 'FILTER-SAW',
                'updated_since' => $since->toAtomString(),
            ]));

        $secondResponse->assertOk();
        $secondResponse->assertJsonPath('data.inventory.summary.total_products', 1);
        $secondResponse->assertJsonPath('data.inventory.summary.low_stock', 1);
        $secondResponse->assertJsonPath('data.inventory.low_stock.0.id', $product->getKey());
    }

    public function test_inventory_limit_parameter_caps_low_and_out_of_stock_lists(): void
    {
        // Generate multiple low and out-of-stock products to exercise the limiter behaviour.
        $lowStockProducts = Product::factory()->count(3)->create([
            'manage_stock'        => true,
            'stock_quantity'      => 2,
            'low_stock_threshold' => 5,
        ]);

        $outOfStockProducts = Product::factory()->count(2)->create([
            'manage_stock'        => true,
            'stock_quantity'      => 0,
            'low_stock_threshold' => 1,
        ]);

        $response = $this
            ->withHeader($this->headerName, $this->plainTextKey)
            ->getJson(route('api.partner.inventory.index', ['limit' => 1]));

        $response->assertOk();

        $this->assertCount(1, $response->json('data.inventory.low_stock'));
        $this->assertCount(1, $response->json('data.inventory.out_of_stock'));
        $this->assertContains($response->json('data.inventory.low_stock.0.id'), $lowStockProducts->modelKeys());
        $this->assertContains($response->json('data.inventory.out_of_stock.0.id'), $outOfStockProducts->modelKeys());
        $response->assertJsonPath('meta.filters.limit', 1);
    }
}
