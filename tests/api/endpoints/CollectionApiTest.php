<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AssertsAgainstOpenApi;
use Tests\TestCase;

final class CollectionApiTest extends TestCase
{
    use AssertsAgainstOpenApi;
    use RefreshDatabase;

    public function test_collections_api_returns_json(): void
    {
        Collection::factory()->count(3)->create(['is_visible' => true]);

        $response = $this->getJson('/collections/api/search');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'is_visible',
                        'is_automatic',
                        'display_type',
                        'products_count',
                    ],
                ],
            ]);

        $this->assertResponseMatchesOpenApi($response, '/collections/api/search');
    }

    public function test_collections_api_search_functionality(): void
    {
        $collection1 = Collection::factory()->create([
            'name' => 'Summer Collection',
            'is_visible' => true,
        ]);
        $collection2 = Collection::factory()->create([
            'name' => 'Winter Collection',
            'is_visible' => true,
        ]);

        $response = $this->getJson('/collections/api/search?search=Summer');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Summer Collection'])
            ->assertJsonMissing(['name' => 'Winter Collection']);

        $this->assertResponseMatchesOpenApi($response, '/collections/api/search');
    }

    public function test_collections_api_by_type_manual(): void
    {
        $manualCollection = Collection::factory()->create([
            'is_automatic' => false,
            'is_visible' => true,
        ]);
        $automaticCollection = Collection::factory()->create([
            'is_automatic' => true,
            'is_visible' => true,
        ]);

        $response = $this->getJson('/collections/api/by-type/manual');

        $response->assertOk()
            ->assertJsonFragment(['name' => $manualCollection->name])
            ->assertJsonMissing(['name' => $automaticCollection->name]);

        $this->assertResponseMatchesOpenApi($response, '/collections/api/by-type/{type}');
    }

    public function test_collections_api_by_type_automatic(): void
    {
        $manualCollection = Collection::factory()->create([
            'is_automatic' => false,
            'is_visible' => true,
        ]);
        $automaticCollection = Collection::factory()->create([
            'is_automatic' => true,
            'is_visible' => true,
        ]);

        $response = $this->getJson('/collections/api/by-type/automatic');

        $response->assertOk()
            ->assertJsonFragment(['name' => $automaticCollection->name])
            ->assertJsonMissing(['name' => $manualCollection->name]);

        $this->assertResponseMatchesOpenApi($response, '/collections/api/by-type/{type}');
    }

    public function test_collections_api_with_products(): void
    {
        $collectionWithProducts = Collection::factory()->create(['is_visible' => true]);
        $collectionWithoutProducts = Collection::factory()->create(['is_visible' => true]);

        $product = Product::factory()->create(['is_visible' => true]);
        $collectionWithProducts->products()->attach($product->id);

        $response = $this->getJson('/collections/api/with-products');

        $response->assertOk()
            ->assertJsonFragment(['name' => $collectionWithProducts->name])
            ->assertJsonMissing(['name' => $collectionWithoutProducts->name]);

        $this->assertResponseMatchesOpenApi($response, '/collections/api/with-products');
    }

    public function test_collections_api_popular(): void
    {
        Collection::factory()->count(5)->create(['is_visible' => true]);

        $response = $this->getJson('/collections/api/popular');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'products_count',
                    ],
                ],
            ]);

        $this->assertResponseMatchesOpenApi($response, '/collections/api/popular');
    }

    public function test_collections_api_statistics(): void
    {
        Collection::factory()->count(3)->create(['is_visible' => true]);
        Collection::factory()->count(2)->create(['is_visible' => false]);
        Collection::factory()->count(2)->create(['is_automatic' => true, 'is_visible' => true]);
        Collection::factory()->count(1)->create(['is_automatic' => false, 'is_visible' => true]);

        $response = $this->getJson('/collections/api/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'total_collections',
                'visible_collections',
                'automatic_collections',
                'manual_collections',
                'collections_with_products',
            ])
            ->assertJson([
                'total_collections' => 8,
                'visible_collections' => 5,
                'automatic_collections' => 2,
                'manual_collections' => 1,
            ]);

        $this->assertResponseMatchesOpenApi($response, '/collections/api/statistics');
    }

    public function test_collection_products_api(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true]);
        $product = Product::factory()->create(['is_visible' => true]);
        $collection->products()->attach($product->id);

        $response = $this->getJson("/collections/{$collection->slug}/products");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'brand',
                        'categories',
                        'media',
                    ],
                ],
            ])
            ->assertJsonFragment(['name' => $product->name]);

        $this->assertResponseMatchesOpenApi($response, '/collections/{collection}/products');
    }

    public function test_collection_products_api_pagination(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true]);
        $products = Product::factory()->count(15)->create(['is_visible' => true]);
        $collection->products()->attach($products->pluck('id'));

        $response = $this->getJson("/collections/{$collection->slug}/products?page=2");

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);

        $this->assertResponseMatchesOpenApi($response, '/collections/{collection}/products');
    }

    public function test_collection_products_api_with_brand_filter(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true]);
        $brand = \App\Models\Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id, 'is_visible' => true]);
        $collection->products()->attach($product->id);

        $response = $this->getJson("/collections/{$collection->slug}/products?brand_id={$brand->id}");

        $response->assertOk()
            ->assertJsonFragment(['name' => $product->name]);

        $this->assertResponseMatchesOpenApi($response, '/collections/{collection}/products');
    }

    public function test_collection_products_api_with_category_filter(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true]);
        $category = \App\Models\Category::factory()->create();
        $product = Product::factory()->create(['is_visible' => true]);
        $product->categories()->attach($category->id);
        $collection->products()->attach($product->id);

        $response = $this->getJson("/collections/{$collection->slug}/products?category_id={$category->id}");

        $response->assertOk()
            ->assertJsonFragment(['name' => $product->name]);

        $this->assertResponseMatchesOpenApi($response, '/collections/{collection}/products');
    }

    public function test_collection_products_api_with_price_filter(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true]);
        $product = Product::factory()->create(['is_visible' => true]);
        $collection->products()->attach($product->id);

        $response = $this->getJson("/collections/{$collection->slug}/products?price_min=100&price_max=500");

        $response->assertOk();

        $this->assertResponseMatchesOpenApi($response, '/collections/{collection}/products');
    }

    public function test_collection_products_api_with_sorting(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true]);
        $product1 = Product::factory()->create(['name' => 'A Product', 'is_visible' => true]);
        $product2 = Product::factory()->create(['name' => 'Z Product', 'is_visible' => true]);
        $collection->products()->attach([$product1->id, $product2->id]);

        $response = $this->getJson("/collections/{$collection->slug}/products?sort=name_asc");

        $response->assertOk();

        $this->assertResponseMatchesOpenApi($response, '/collections/{collection}/products');
    }

    public function test_collection_products_api_empty_collection(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true]);

        $response = $this->getJson("/collections/{$collection->slug}/products");

        $response->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertExactJson($this->jsonFixture('api/collections/products-empty.json'));

        $this->assertResponseMatchesOpenApi($response, '/collections/{collection}/products');
    }

    public function test_collection_products_api_hidden_collection_returns_404(): void
    {
        $collection = Collection::factory()->create(['is_visible' => false]);

        $response = $this->getJson("/collections/{$collection->slug}/products");

        $response->assertNotFound()
            ->assertExactJson($this->jsonFixture('api/errors/not-found.json'));

        $this->assertResponseMatchesOpenApi($response, '/collections/{collection}/products');
    }

    public function test_collection_products_api_inactive_collection_returns_404(): void
    {
        $collection = Collection::factory()->create(['is_active' => false]);

        $response = $this->getJson("/collections/{$collection->slug}/products");

        $response->assertNotFound()
            ->assertExactJson($this->jsonFixture('api/errors/not-found.json'));

        $this->assertResponseMatchesOpenApi($response, '/collections/{collection}/products');
    }

    public function test_collections_api_returns_only_visible_collections(): void
    {
        $visibleCollection = Collection::factory()->create(['is_visible' => true]);
        $hiddenCollection = Collection::factory()->create(['is_visible' => false]);

        $response = $this->getJson('/collections/api/search');

        $response->assertOk()
            ->assertJsonFragment(['name' => $visibleCollection->name])
            ->assertJsonMissing(['name' => $hiddenCollection->name]);

        $this->assertResponseMatchesOpenApi($response, '/collections/api/search');
    }

    public function test_collections_api_returns_only_active_collections(): void
    {
        $activeCollection = Collection::factory()->create(['is_active' => true, 'is_visible' => true]);
        $inactiveCollection = Collection::factory()->create(['is_active' => false, 'is_visible' => true]);

        $response = $this->getJson('/collections/api/search');

        $response->assertOk()
            ->assertJsonFragment(['name' => $activeCollection->name])
            ->assertJsonMissing(['name' => $inactiveCollection->name]);

        $this->assertResponseMatchesOpenApi($response, '/collections/api/search');
    }

    public function test_collections_api_with_limit_parameter(): void
    {
        Collection::factory()->count(10)->create(['is_visible' => true]);

        $response = $this->getJson('/collections/api/search?limit=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data');

        $this->assertResponseMatchesOpenApi($response, '/collections/api/search');
    }

    public function test_collections_api_with_offset_parameter(): void
    {
        Collection::factory()->count(10)->create(['is_visible' => true]);

        $response = $this->getJson('/collections/api/search?offset=5&limit=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data');

        $this->assertResponseMatchesOpenApi($response, '/collections/api/search');
    }

    public function test_collections_api_with_display_type_filter(): void
    {
        $gridCollection = Collection::factory()->create(['display_type' => 'grid', 'is_visible' => true]);
        $listCollection = Collection::factory()->create(['display_type' => 'list', 'is_visible' => true]);

        $response = $this->getJson('/collections/api/search?display_type=grid');

        $response->assertOk()
            ->assertJsonFragment(['name' => $gridCollection->name])
            ->assertJsonMissing(['name' => $listCollection->name]);

        $this->assertResponseMatchesOpenApi($response, '/collections/api/search');
    }

    public function test_collections_api_with_products_count_filter(): void
    {
        $collectionWithProducts = Collection::factory()->create(['is_visible' => true]);
        $collectionWithoutProducts = Collection::factory()->create(['is_visible' => true]);

        $product = Product::factory()->create(['is_visible' => true]);
        $collectionWithProducts->products()->attach($product->id);

        $response = $this->getJson('/collections/api/search?has_products=true');

        $response->assertOk()
            ->assertJsonFragment(['name' => $collectionWithProducts->name])
            ->assertJsonMissing(['name' => $collectionWithoutProducts->name]);

        $this->assertResponseMatchesOpenApi($response, '/collections/api/search');
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonFixture(string $relativePath): array
    {
        $path = base_path('tests/Fixtures/'.$relativePath);

        if (! is_file($path)) {
            self::fail(sprintf('The JSON fixture %s could not be found.', $relativePath));
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            self::fail(sprintf('The JSON fixture %s could not be read.', $relativePath));
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }
}
