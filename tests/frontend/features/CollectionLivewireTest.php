<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Pages\Collection\Index;
use App\Livewire\Pages\Collection\Show;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CollectionLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_index_component_renders(): void
    {
        Collection::factory()->count(3)->create(['is_visible' => true, 'is_active' => true]);

        Livewire::test(Index::class)
            ->assertOk()
            ->assertViewIs('livewire.pages.collection.index');
    }

    public function test_collection_index_shows_collections(): void
    {
        $collection = Collection::factory()->create([
            'name' => 'Test Collection',
            'is_visible' => true,
            'is_active' => true,
        ]);

        Livewire::test(Index::class)
            ->assertSee($collection->name);
    }

    public function test_collection_index_hides_invisible_collections(): void
    {
        $visibleCollection = Collection::factory()->create([
            'name' => 'Visible Collection',
            'is_visible' => true,
            'is_active' => true,
        ]);
        
        $hiddenCollection = Collection::factory()->create([
            'name' => 'Hidden Collection',
            'is_visible' => false,
            'is_active' => true,
        ]);

        Livewire::test(Index::class)
            ->assertSee($visibleCollection->name)
            ->assertDontSee($hiddenCollection->name);
    }

    public function test_collection_index_hides_inactive_collections(): void
    {
        $activeCollection = Collection::factory()->create([
            'name' => 'Active Collection',
            'is_visible' => true,
            'is_active' => true,
        ]);
        
        $inactiveCollection = Collection::factory()->create([
            'name' => 'Inactive Collection',
            'is_visible' => true,
            'is_active' => false,
        ]);

        Livewire::test(Index::class)
            ->assertSee($activeCollection->name)
            ->assertDontSee($inactiveCollection->name);
    }

    public function test_collection_index_loads_products_for_each_collection(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);
        $product = Product::factory()->create(['is_visible' => true]);
        $collection->products()->attach($product->id);

        Livewire::test(Index::class)
            ->assertOk();
    }

    public function test_collection_show_component_renders(): void
    {
        $collection = Collection::factory()->create([
            'is_visible' => true,
            'is_active' => true,
        ]);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertOk()
            ->assertViewIs('livewire.pages.collection.show');
    }

    public function test_collection_show_displays_collection_name(): void
    {
        $collection = Collection::factory()->create([
            'name' => 'Test Collection',
            'is_visible' => true,
            'is_active' => true,
        ]);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertSee($collection->name);
    }

    public function test_collection_show_displays_collection_description(): void
    {
        $collection = Collection::factory()->create([
            'description' => 'Test Description',
            'is_visible' => true,
            'is_active' => true,
        ]);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertSee($collection->description);
    }

    public function test_collection_show_returns_404_for_inactive_collection(): void
    {
        $collection = Collection::factory()->create(['is_active' => false]);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertStatus(404);
    }

    public function test_collection_show_returns_404_for_hidden_collection(): void
    {
        $collection = Collection::factory()->create(['is_visible' => false]);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertStatus(404);
    }

    public function test_collection_show_displays_products(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);
        $product = Product::factory()->create(['is_visible' => true]);
        $collection->products()->attach($product->id);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertSee($product->name);
    }

    public function test_collection_show_brand_filtering(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id, 'is_visible' => true]);
        $collection->products()->attach($product->id);

        Livewire::test(Show::class, ['collection' => $collection])
            ->set('brandIds', [$brand->id])
            ->assertSee($product->name);
    }

    public function test_collection_show_sorting(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);
        $product1 = Product::factory()->create(['name' => 'A Product', 'is_visible' => true]);
        $product2 = Product::factory()->create(['name' => 'Z Product', 'is_visible' => true]);
        $collection->products()->attach([$product1->id, $product2->id]);

        Livewire::test(Show::class, ['collection' => $collection])
            ->set('sort', 'name_asc')
            ->assertOk();
    }

    public function test_collection_show_pagination(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);
        $products = Product::factory()->count(15)->create(['is_visible' => true]);
        $collection->products()->attach($products->pluck('id'));

        Livewire::test(Show::class, ['collection' => $collection])
            ->set('page', 2)
            ->assertOk();
    }

    public function test_collection_show_empty_state(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertSee('No products in this collection');
    }

    public function test_collection_show_redirects_to_canonical_slug(): void
    {
        $collection = Collection::factory()->create(['slug' => 'old-slug']);
        
        \App\Models\Translations\CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'locale' => 'lt',
            'slug' => 'new-slug',
        ]);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertRedirect('/lt/collections/new-slug');
    }

    public function test_collection_show_feature_disabled_returns_404(): void
    {
        config(['app-features.features.collection' => false]);

        $collection = Collection::factory()->create();

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertStatus(404);
    }

    public function test_collection_index_feature_disabled_returns_404(): void
    {
        config(['app-features.features.collection' => false]);

        Livewire::test(Index::class)
            ->assertStatus(404);
    }

    public function test_collection_show_related_collections(): void
    {
        $collection = Collection::factory()->create([
            'is_visible' => true,
            'is_active' => true,
            'display_type' => 'grid',
        ]);
        
        $relatedCollection = Collection::factory()->create([
            'is_visible' => true,
            'is_active' => true,
            'display_type' => 'grid',
        ]);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertOk();
    }

    public function test_collection_show_products_with_media(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);
        $product = Product::factory()->create(['is_visible' => true]);
        $collection->products()->attach($product->id);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertOk();
    }

    public function test_collection_show_products_with_brand(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id, 'is_visible' => true]);
        $collection->products()->attach($product->id);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertSee($brand->name);
    }

    public function test_collection_show_products_with_categories(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);
        $category = \App\Models\Category::factory()->create();
        $product = Product::factory()->create(['is_visible' => true]);
        $product->categories()->attach($category->id);
        $collection->products()->attach($product->id);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertSee($category->name);
    }

    public function test_collection_show_loading_state(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertSee('Loading…');
    }

    public function test_collection_show_error_handling(): void
    {
        $collection = Collection::factory()->create(['is_visible' => true, 'is_active' => true]);

        Livewire::test(Show::class, ['collection' => $collection])
            ->assertOk();
    }
}
