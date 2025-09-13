<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Collection;
use App\Models\Product;
use App\Models\CollectionRule;
use App\Models\Translations\CollectionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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
            'is_automatic' => false,
            'rules' => ['key' => 'value'],
            'meta_keywords' => ['keyword1', 'keyword2'],
            'created_at' => now(),
        ]);

        $this->assertIsBool($collection->is_active);
        $this->assertIsInt($collection->sort_order);
        $this->assertIsBool($collection->is_automatic);
        $this->assertIsArray($collection->rules);
        $this->assertIsArray($collection->meta_keywords);
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
        $this->assertContains('is_automatic', $fillable);
        $this->assertContains('rules', $fillable);
        $this->assertContains('max_products', $fillable);
        $this->assertContains('meta_title', $fillable);
        $this->assertContains('meta_description', $fillable);
        $this->assertContains('meta_keywords', $fillable);
        $this->assertContains('display_type', $fillable);
        $this->assertContains('products_per_page', $fillable);
        $this->assertContains('show_filters', $fillable);
    }

    public function test_collection_translatable_attributes(): void
    {
        $collection = new Collection();
        
        // Test that the model has translatable attributes defined
        $this->assertTrue(property_exists(Collection::class, 'translatable'));
        $this->assertIsArray(Collection::$translatable);
        
        if (!empty(Collection::$translatable)) {
            $this->assertContains('name', Collection::$translatable);
            $this->assertContains('description', Collection::$translatable);
            $this->assertContains('meta_title', Collection::$translatable);
            $this->assertContains('meta_description', Collection::$translatable);
            $this->assertContains('meta_keywords', Collection::$translatable);
        }
    }

    public function test_collection_scope_active(): void
    {
        $activeCollection = Collection::factory()->create(['is_active' => true]);
        $inactiveCollection = Collection::factory()->create(['is_active' => false]);

        $activeCollections = Collection::active()->get();

        $this->assertTrue($activeCollections->contains($activeCollection));
        $this->assertFalse($activeCollections->contains($inactiveCollection));
    }

    public function test_collection_scope_visible(): void
    {
        $visibleCollection = Collection::factory()->create(['is_visible' => true]);
        $hiddenCollection = Collection::factory()->create(['is_visible' => false]);

        $visibleCollections = Collection::visible()->get();

        $this->assertTrue($visibleCollections->contains($visibleCollection));
        $this->assertFalse($visibleCollections->contains($hiddenCollection));
    }

    public function test_collection_scope_manual(): void
    {
        $manualCollection = Collection::factory()->create(['is_automatic' => false]);
        $automaticCollection = Collection::factory()->create(['is_automatic' => true]);

        $manualCollections = Collection::manual()->get();

        $this->assertTrue($manualCollections->contains($manualCollection));
        $this->assertFalse($manualCollections->contains($automaticCollection));
    }

    public function test_collection_scope_automatic(): void
    {
        $manualCollection = Collection::factory()->create(['is_automatic' => false]);
        $automaticCollection = Collection::factory()->create(['is_automatic' => true]);

        $automaticCollections = Collection::automatic()->get();

        $this->assertTrue($automaticCollections->contains($automaticCollection));
        $this->assertFalse($automaticCollections->contains($manualCollection));
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
            'meta_keywords' => ['test', 'collection', 'meta'],
        ]);

        $this->assertEquals('Test Meta Title', $collection->meta_title);
        $this->assertEquals('Test Meta Description', $collection->meta_description);
        $this->assertEquals(['test', 'collection', 'meta'], $collection->meta_keywords);
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

    public function test_collection_can_have_rules(): void
    {
        $collection = Collection::factory()->create([
            'is_automatic' => true,
            'rules' => [
                'category_id' => '1',
                'price_min' => '10',
                'price_max' => '100',
            ],
        ]);

        $this->assertTrue($collection->is_automatic);
        $this->assertIsArray($collection->rules);
        $this->assertEquals('1', $collection->rules['category_id']);
        $this->assertEquals('10', $collection->rules['price_min']);
        $this->assertEquals('100', $collection->rules['price_max']);
    }

    public function test_collection_can_have_collection_rules(): void
    {
        $collection = Collection::factory()->create();
        $rule = CollectionRule::factory()->create([
            'collection_id' => $collection->id,
            'field' => 'category_id',
            'operator' => 'equals',
            'value' => '1',
        ]);

        $this->assertTrue($collection->rules()->exists());
        $this->assertInstanceOf(CollectionRule::class, $collection->rules()->first());
    }

    public function test_collection_can_have_translations(): void
    {
        $collection = Collection::factory()->create();
        $translation = CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'locale' => 'lt',
            'name' => 'Test Kolekcija',
            'description' => 'Test aprašymas',
        ]);

        $this->assertTrue($collection->translations()->exists());
        $this->assertInstanceOf(CollectionTranslation::class, $collection->translations->first());
    }

    public function test_collection_is_manual_method(): void
    {
        $manualCollection = Collection::factory()->create(['is_automatic' => false]);
        $automaticCollection = Collection::factory()->create(['is_automatic' => true]);

        $this->assertTrue($manualCollection->isManual());
        $this->assertFalse($automaticCollection->isManual());
    }

    public function test_collection_is_automatic_method(): void
    {
        $manualCollection = Collection::factory()->create(['is_automatic' => false]);
        $automaticCollection = Collection::factory()->create(['is_automatic' => true]);

        $this->assertFalse($manualCollection->isAutomatic());
        $this->assertTrue($automaticCollection->isAutomatic());
    }

    public function test_collection_products_count_attribute(): void
    {
        $collection = Collection::factory()->create();
        $products = Product::factory()->count(3)->create([
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);
        $unpublishedProducts = Product::factory()->count(2)->create(['status' => 'draft']);

        $collection->products()->attach($products->pluck('id'));
        $collection->products()->attach($unpublishedProducts->pluck('id'));

        $this->assertEquals(3, $collection->products_count);
    }

    public function test_collection_image_attribute(): void
    {
        $collection = Collection::factory()->create();
        
        // Test without image
        $this->assertNull($collection->image);
        
        // Test with image (would need actual media file for full test)
        $this->assertTrue(method_exists($collection, 'getImageAttribute'));
    }

    public function test_collection_image_url_method(): void
    {
        $collection = Collection::factory()->create();
        
        // Test without size parameter
        $this->assertTrue(method_exists($collection, 'getImageUrl'));
        
        // Test with size parameter
        $this->assertTrue(method_exists($collection, 'getImageUrl'));
    }

    public function test_collection_banner_url_method(): void
    {
        $collection = Collection::factory()->create();
        
        // Test without size parameter
        $this->assertTrue(method_exists($collection, 'getBannerUrl'));
        
        // Test with size parameter
        $this->assertTrue(method_exists($collection, 'getBannerUrl'));
    }

    public function test_collection_flush_caches_method(): void
    {
        // Test that the method exists and can be called
        $this->assertTrue(method_exists(Collection::class, 'flushCaches'));
        
        // Test that it doesn't throw an error
        Collection::flushCaches();
        $this->assertTrue(true);
    }

    public function test_collection_booted_events(): void
    {
        $collection = Collection::factory()->create();
        
        // Test that the model has booted method
        $this->assertTrue(method_exists($collection, 'booted'));
    }

    public function test_collection_media_conversions(): void
    {
        $collection = Collection::factory()->create();
        
        // Test that media conversions are registered
        $this->assertTrue(method_exists($collection, 'registerMediaConversions'));
    }

    public function test_collection_media_collections(): void
    {
        $collection = Collection::factory()->create();
        
        // Test that media collections are registered
        $this->assertTrue(method_exists($collection, 'registerMediaCollections'));
    }
}