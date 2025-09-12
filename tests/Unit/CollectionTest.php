<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_can_be_created(): void
    {
        $collection = Collection::factory()->create([
            'name' => 'Test Collection',
            'slug' => 'test-collection',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('collections', [
            'name' => 'Test Collection',
            'slug' => 'test-collection',
            'is_active' => true,
        ]);
    }

    public function test_collection_can_have_many_products(): void
    {
        $collection = Collection::factory()->create();
        $products = Product::factory()->count(3)->create();

        $collection->products()->attach($products->pluck('id'));

        $this->assertCount(3, $collection->products);
        $this->assertInstanceOf(Product::class, $collection->products->first());
    }

    public function test_collection_has_media_relationship(): void
    {
        $collection = Collection::factory()->create();

        // Test that collection implements HasMedia
        $this->assertInstanceOf(\Spatie\MediaLibrary\HasMedia::class, $collection);
        
        // Test that collection can handle media
        $this->assertTrue(method_exists($collection, 'registerMediaCollections'));
        $this->assertTrue(method_exists($collection, 'registerMediaConversions'));
        $this->assertTrue(method_exists($collection, 'media'));
    }

    public function test_collection_has_translations_relationship(): void
    {
        $collection = Collection::factory()->create();

        // Test that collection has translations relationship
        $this->assertTrue(method_exists($collection, 'translations'));
        $this->assertTrue(method_exists($collection, 'trans'));
    }

    public function test_collection_route_key_name(): void
    {
        $collection = new Collection();
        $this->assertEquals('slug', $collection->getRouteKeyName());
    }

    public function test_collection_casts_work_correctly(): void
    {
        $collection = Collection::factory()->create([
            'is_active' => true,
            'sort_order' => 5,
            'created_at' => now(),
        ]);

        $this->assertIsBool($collection->is_active);
        $this->assertIsInt($collection->sort_order);
        $this->assertInstanceOf(\Carbon\Carbon::class, $collection->created_at);
    }

    public function test_collection_fillable_attributes(): void
    {
        $collection = new Collection();
        $fillable = $collection->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_collection_scope_active(): void
    {
        $activeCollection = Collection::factory()->create(['is_active' => true]);
        $inactiveCollection = Collection::factory()->create(['is_active' => false]);

        $activeCollections = Collection::active()->get();

        $this->assertTrue($activeCollections->contains($activeCollection));
        $this->assertFalse($activeCollections->contains($inactiveCollection));
    }

    public function test_collection_scope_ordered(): void
    {
        $collection1 = Collection::factory()->create(['sort_order' => 2]);
        $collection2 = Collection::factory()->create(['sort_order' => 1]);
        $collection3 = Collection::factory()->create(['sort_order' => 3]);

        $orderedCollections = Collection::ordered()->get();

        $this->assertEquals($collection2->id, $orderedCollections->first()->id);
        $this->assertEquals($collection3->id, $orderedCollections->last()->id);
    }

    public function test_collection_can_have_featured_products(): void
    {
        $collection = Collection::factory()->create();
        $products = Product::factory()->count(5)->create();

        $collection->products()->attach($products->pluck('id'));

        $this->assertCount(5, $collection->products);
    }

    public function test_collection_can_have_meta_information(): void
    {
        $collection = Collection::factory()->create([
            'meta_title' => 'Test Meta Title',
            'meta_description' => 'Test Meta Description',
            'meta_keywords' => 'test, collection, meta',
        ]);

        $this->assertEquals('Test Meta Title', $collection->meta_title);
        $this->assertEquals('Test Meta Description', $collection->meta_description);
        $this->assertEquals('test, collection, meta', $collection->meta_keywords);
    }

    public function test_collection_can_have_display_settings(): void
    {
        $collection = Collection::factory()->create([
            'display_type' => 'grid',
            'products_per_page' => 12,
            'show_filters' => true,
        ]);

        $this->assertEquals('grid', $collection->display_type);
        $this->assertEquals(12, $collection->products_per_page);
        $this->assertTrue($collection->show_filters);
    }
}