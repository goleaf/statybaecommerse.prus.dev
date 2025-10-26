<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\ApiKey;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
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
        // Create a full inventory record so the API can surface product, variant, and location context.
        $product = Product::factory()->create([
            'name' => 'Partner Drill',
            'sku'  => 'PARTNER-DRILL',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku'        => 'PARTNER-DRILL-001',
            'name'       => 'Partner Drill Variant',
        ]);

        $location = Location::factory()->create([
            'name' => 'Vilnius Warehouse',
            'code' => 'VIL-001',
        ]);

        $inventory = VariantInventory::factory()->create([
            'variant_id'        => $variant->id,
            'location_id'       => $location->id,
            'warehouse_code'    => 'WH-001',
            'stock'             => 25,
            'reserved'          => 5,
            'available'         => 20,
            'incoming'          => 3,
            'threshold'         => 2,
            'reorder_point'     => 4,
            'reorder_quantity'  => 7,
            'max_stock_level'   => 50,
            'cost_per_unit'     => 12.50,
            'is_tracked'        => true,
            'status'            => 'active',
            'last_restocked_at' => CarbonImmutable::now()->subDay(),
            'last_sold_at'      => CarbonImmutable::now()->subHours(2),
        ]);

        $response = $this
            ->withHeader($this->headerName, $this->plainTextKey)
            ->getJson(route('api.partner.inventory.index', ['per_page' => 10]));

        $response->assertOk();

        // Confirm the inventory payload includes the key identifiers and stock metrics we expose to partners.
        $response->assertJsonPath('data.inventory.0.id', $inventory->getKey());
        $response->assertJsonPath('data.inventory.0.product_sku', $product->sku);
        $response->assertJsonPath('data.inventory.0.variant_sku', $variant->sku);
        $response->assertJsonPath('data.inventory.0.location.code', $location->code);
        $response->assertJsonPath('data.inventory.0.stock', 25);
        $response->assertJsonPath('data.inventory.0.status.code', 'in_stock');

        // Ensure pagination metadata is surfaced so integrations can request subsequent pages.
        $response->assertJsonPath('meta.pagination.per_page', 10);
        $scopes = $response->json('meta.scopes');
        $scopes = is_array($scopes) ? $scopes : [];
        $this->assertContains('inventory.read', $scopes);
    }

    public function test_inventory_endpoint_supports_incremental_filters(): void
    {
        $product = Product::factory()->create([
            'name' => 'Filter Saw',
            'sku'  => 'FILTER-SAW',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku'        => 'FILTER-SAW-01',
        ]);

        $location = Location::factory()->create([
            'name' => 'Kaunas Warehouse',
            'code' => 'KAU-002',
        ]);

        $stale = VariantInventory::factory()->create([
            'variant_id'     => $variant->id,
            'location_id'    => $location->id,
            'warehouse_code' => 'WH-STALE',
            'stock'          => 5,
            'reserved'       => 1,
            'available'      => 4,
            'status'         => 'active',
        ]);
        $stale->forceFill(['updated_at' => CarbonImmutable::now()->subDays(2)])->save();

        $recent = VariantInventory::factory()->create([
            'variant_id'     => $variant->id,
            'location_id'    => $location->id,
            'warehouse_code' => 'WH-RECENT',
            'stock'          => 9,
            'reserved'       => 2,
            'available'      => 7,
            'status'         => 'active',
        ]);
        $recent->forceFill(['updated_at' => CarbonImmutable::now()->subHours(1)])->save();

        $since = CarbonImmutable::now()->subDay();

        // Request inventory updated in the last 24 hours to make sure only fresh records remain.
        $response = $this
            ->withHeader($this->headerName, $this->plainTextKey)
            ->getJson(route('api.partner.inventory.index', [
                'updated_since' => $since->toAtomString(),
                'sku'           => 'FILTER-SAW',
            ]));

        $response->assertOk();

        // The stale record should be absent while the recent entry is returned.
        $inventory = $response->json('data.inventory');
        $inventory = is_array($inventory) ? $inventory : [];
        $inventoryIds = array_column($inventory, 'id');
        $this->assertContains($recent->getKey(), $inventoryIds);
        $this->assertNotContains($stale->getKey(), $inventoryIds);
        $response->assertJsonPath('meta.filters.updated_since', $since->toAtomString());
    }

    public function test_inventory_pagination_links_retain_active_filters(): void
    {
        // Set up a product/variant combination that will yield multiple inventory rows.
        $product = Product::factory()->create([
            'name' => 'Paginated Drill',
            'sku'  => 'PAGINATED-DRILL',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku'        => 'PAGINATED-DRILL-01',
        ]);

        $location = Location::factory()->create([
            'name' => 'Klaipeda Warehouse',
            'code' => 'KLA-003',
        ]);

        // Create two inventory records to guarantee at least two pages when per_page=1.
        VariantInventory::factory()->create([
            'variant_id'     => $variant->id,
            'location_id'    => $location->id,
            'warehouse_code' => 'WH-PAGE-1',
            'stock'          => 15,
            'reserved'       => 4,
            'available'      => 11,
            'status'         => 'active',
        ]);

        VariantInventory::factory()->create([
            'variant_id'     => $variant->id,
            'location_id'    => $location->id,
            'warehouse_code' => 'WH-PAGE-2',
            'stock'          => 12,
            'reserved'       => 2,
            'available'      => 10,
            'status'         => 'active',
        ]);

        $since = CarbonImmutable::now()->subHour();

        // Request the first page with filters so we can inspect the generated pagination URLs.
        $firstResponse = $this
            ->withHeader($this->headerName, $this->plainTextKey)
            ->getJson(route('api.partner.inventory.index', [
                'per_page'      => 1,
                'sku'           => 'PAGINATED-DRILL',
                'updated_since' => $since->toAtomString(),
            ]));

        $firstResponse->assertOk();

        $nextUrl = $firstResponse->json('meta.pagination.links.next');
        $this->assertIsString($nextUrl);

        // Parse the URL and make sure the filters were preserved for the next page link.
        $query = parse_url($nextUrl, PHP_URL_QUERY);
        $query = is_string($query) ? $query : '';
        parse_str($query, $nextParameters);

        $this->assertSame('1', $nextParameters['per_page'] ?? null);
        $this->assertSame('PAGINATED-DRILL', $nextParameters['sku'] ?? null);
        $this->assertSame($since->toAtomString(), $nextParameters['updated_since'] ?? null);

        // Fetch the second page and confirm the previous link also carries the filter context.
        $secondResponse = $this
            ->withHeader($this->headerName, $this->plainTextKey)
            ->getJson(route('api.partner.inventory.index', [
                'per_page'      => 1,
                'page'          => 2,
                'sku'           => 'PAGINATED-DRILL',
                'updated_since' => $since->toAtomString(),
            ]));

        $secondResponse->assertOk();

        $previousUrl = $secondResponse->json('meta.pagination.links.prev');
        $this->assertIsString($previousUrl);

        $previousQuery = parse_url($previousUrl, PHP_URL_QUERY);
        $previousQuery = is_string($previousQuery) ? $previousQuery : '';
        parse_str($previousQuery, $previousParameters);

        $this->assertSame('1', $previousParameters['per_page'] ?? null);
        $this->assertSame('PAGINATED-DRILL', $previousParameters['sku'] ?? null);
        $this->assertSame($since->toAtomString(), $previousParameters['updated_since'] ?? null);
    }
}
