<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CollectionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_collections_index_page_loads_successfully(): void
    {
        Collection::factory()->count(3)->create(['is_visible' => true, 'is_active' => true]);

        $response = $this->get('/lt/collections');

        $response->assertOk()
            ->assertViewIs('livewire.pages.collection.index')
    }

    public function test_collections_index_shows_only_visible_collections(): void
    {
        $visibleCollection = Collection::factory()->create(['is_visible' => true, 'name' => 'Visible Collection']);
        $hiddenCollection = Collection::factory()->create(['is_visible' => false, 'name' => 'Hidden Collection']);

        $response = $this->get('/lt/collections');

        $response->assertOk()
            ->assertSee($visibleCollection->name)
            ->assertDontSee($hiddenCollection->name);
    }

    public function test_collection_show_page_loads_successfully(): void
    {
        $collection = Collection::factory()->create([
            'is_visible' => true,
            'is_active' => true,
            'name' => 'Test Collection',
            'description' => 'Test Description',
        ]);

        $response = $this->get("/lt/collections/{$collection->slug}");

        $response->assertOk()
            ->assertViewIs('livewire.pages.collection.show')
            ->assertSee($collection->name);
    }

    public function test_collection_show_page_returns_404_for_inactive_collection(): void
    {
        $collection = Collection::factory()->create(['is_active' => false]);

        $response = $this->get("/lt/collections/{$collection->slug}");

        $response->assertNotFound();
    }

    public function test_collection_show_page_returns_404_for_hidden_collection(): void
    {
        $collection = Collection::factory()->create(['is_visible' => false]);

        $response = $this->get("/lt/collections/{$collection->slug}");

        $response->assertNotFound();
    }

    public function test_collections_index_with_search_filter(): void
    {
        $collection1 = Collection::factory()->create(['name' => 'Summer Collection', 'is_visible' => true]);
        $collection2 = Collection::factory()->create(['name' => 'Winter Collection', 'is_visible' => true]);

        $response = $this->get('/lt/collections?search=Summer');

        $response->assertOk()
            ->assertSee($collection1->name)
            ->assertDontSee($collection2->name);
    }

    public function test_collections_index_with_type_filter(): void
    {
        $manualCollection = Collection::factory()->create(['is_automatic' => false, 'is_visible' => true]);
        $automaticCollection = Collection::factory()->create(['is_automatic' => true, 'is_visible' => true]);

        $response = $this->get('/lt/collections?type=manual');

        $response->assertOk();
    }

    public function test_collections_index_with_display_type_filter(): void
    {
        $gridCollection = Collection::factory()->create(['display_type' => 'grid', 'is_visible' => true]);
        $listCollection = Collection::factory()->create(['display_type' => 'list', 'is_visible' => true]);

        $response = $this->get('/lt/collections?display_type=grid');

        $response->assertOk();
    }

    public function test_collection_show_displays_products(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);
        $product = Product::factory()->create(['is_visible' => true]);
        $collection->products()->attach($product->id);

        $response = $this->get("/lt/collections/{$collection->slug}");

        $response->assertOk()
            ->assertSee($product->name);
    }

    public function test_collection_show_with_brand_filter(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);
        $brand = \App\Models\Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id, 'is_visible' => true]);
        $collection->products()->attach($product->id);

        $response = $this->get("/lt/collections/{$collection->slug}?brandIds[]={$brand->id}");

        $response->assertOk();
    }

    public function test_collection_show_with_sorting(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);
        $product1 = Product::factory()->create(['name' => 'A Product', 'is_visible' => true]);
        $product2 = Product::factory()->create(['name' => 'Z Product', 'is_visible' => true]);
        $collection->products()->attach([$product1->id, $product2->id]);

        $response = $this->get("/lt/collections/{$collection->slug}?sort=name_asc");

        $response->assertOk();
    }

    public function test_collection_show_pagination(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);
        $products = Product::factory()->count(15)->create(['is_visible' => true]);
        $collection->products()->attach($products->pluck('id'));

        $response = $this->get("/lt/collections/{$collection->slug}");

        $response->assertOk();
    }

    public function test_collection_show_empty_state(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);

        $response = $this->get("/lt/collections/{$collection->slug}");

        $response->assertOk()
            ->assertSee('No products in this collection');
    }

    public function test_collection_redirects_to_canonical_slug(): void
    {
        $collection = Collection::factory()->create(['slug' => 'old-slug']);
        
        // Create translation with different slug
        \App\Models\Translations\CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'locale' => 'lt',
            'slug' => 'new-slug',
        ]);

        $response = $this->get("/lt/collections/{$collection->slug}");

        $response->assertRedirect("/lt/collections/new-slug");
    }

    public function test_collections_api_endpoint(): void
    {
        Collection::factory()->count(3)->create(['is_visible' => true]);

        $response = $this->get('/collections/api/search');

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
                    ]
                ]
            ]);
    }

    public function test_collections_api_with_search(): void
    {
        $collection1 = Collection::factory()->create(['name' => 'Summer Collection', 'is_visible' => true]);
        $collection2 = Collection::factory()->create(['name' => 'Winter Collection', 'is_visible' => true]);

        $response = $this->get('/collections/api/search?search=Summer');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Summer Collection'])
            ->assertJsonMissing(['name' => 'Winter Collection']);
    }

    public function test_collections_api_by_type(): void
    {
        $manualCollection = Collection::factory()->create(['is_automatic' => false, 'is_visible' => true]);
        $automaticCollection = Collection::factory()->create(['is_automatic' => true, 'is_visible' => true]);

        $response = $this->get('/collections/api/by-type/manual');

        $response->assertOk()
            ->assertJsonFragment(['name' => $manualCollection->name])
            ->assertJsonMissing(['name' => $automaticCollection->name]);
    }

    public function test_collections_api_with_products(): void
    {
        $collectionWithProducts = Collection::factory()->create(['is_visible' => true]);
        $collectionWithoutProducts = Collection::factory()->create(['is_visible' => true]);
        
        $product = Product::factory()->create(['is_visible' => true]);
        $collectionWithProducts->products()->attach($product->id);

        $response = $this->get('/collections/api/with-products');

        $response->assertOk()
            ->assertJsonFragment(['name' => $collectionWithProducts->name])
            ->assertJsonMissing(['name' => $collectionWithoutProducts->name]);
    }

    public function test_collections_api_popular(): void
    {
        Collection::factory()->count(5)->create(['is_visible' => true]);

        $response = $this->get('/collections/api/popular');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'products_count',
                    ]
                ]
            ]);
    }

    public function test_collections_api_statistics(): void
    {
        Collection::factory()->count(3)->create(['is_visible' => true]);

        $response = $this->get('/collections/api/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'total_collections',
                'visible_collections',
                'automatic_collections',
                'manual_collections',
                'collections_with_products',
            ]);
    }

    public function test_collection_products_endpoint(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true]);
        $product = Product::factory()->create(['is_visible' => true]);
        $collection->products()->attach($product->id);

        $response = $this->get("/collections/{$collection->slug}/products");

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
                    ]
                ]
            ]);
    }

    public function test_collection_feature_disabled_returns_404(): void
    {
        // Temporarily disable collection feature
        config(['app-features.features.collection' => false]);

        $response = $this->get('/lt/collections');

        $response->assertNotFound();
    }

    public function test_collection_show_feature_disabled_returns_404(): void
    {
        config(['app-features.features.collection' => false]);

        $collection = Collection::factory()->create();

        $response = $this->get("/lt/collections/{$collection->slug}");

        $response->assertNotFound();
    }
}
