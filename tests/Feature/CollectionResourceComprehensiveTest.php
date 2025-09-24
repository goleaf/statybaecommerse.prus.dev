<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CollectionResource;
use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CollectionResourceComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'is_admin' => true,
        ]);
    }

    public function test_collection_resource_can_be_instantiated(): void
    {
        $this->assertInstanceOf(CollectionResource::class, new CollectionResource);
    }

    public function test_collection_resource_has_correct_model(): void
    {
        $reflection = new \ReflectionClass(CollectionResource::class);
        $modelProperty = $reflection->getProperty('model');
        $modelProperty->setAccessible(true);

        $this->assertEquals(Collection::class, $modelProperty->getValue());
    }

    public function test_collection_resource_has_correct_navigation_group(): void
    {
        $reflection = new \ReflectionClass(CollectionResource::class);
        $navigationGroupProperty = $reflection->getProperty('navigationGroup');
        $navigationGroupProperty->setAccessible(true);

        $this->assertEquals('Products', $navigationGroupProperty->getValue());
    }

    public function test_collection_resource_form_method_exists(): void
    {
        $this->assertTrue(method_exists(CollectionResource::class, 'form'));
    }

    public function test_collection_resource_table_method_exists(): void
    {
        $this->assertTrue(method_exists(CollectionResource::class, 'table'));
    }

    public function test_collection_resource_get_pages_method_exists(): void
    {
        $this->assertTrue(method_exists(CollectionResource::class, 'getPages'));
    }

    public function test_collection_resource_get_relations_method_exists(): void
    {
        $this->assertTrue(method_exists(CollectionResource::class, 'getRelations'));
    }

    public function test_collection_resource_get_navigation_label(): void
    {
        $this->assertIsString(CollectionResource::getNavigationLabel());
    }

    public function test_collection_resource_get_navigation_group(): void
    {
        $this->assertIsString(CollectionResource::getNavigationGroup());
        $this->assertEquals('Products', CollectionResource::getNavigationGroup());
    }

    public function test_collection_resource_get_plural_model_label(): void
    {
        $this->assertIsString(CollectionResource::getPluralModelLabel());
    }

    public function test_collection_resource_get_model_label(): void
    {
        $this->assertIsString(CollectionResource::getModelLabel());
    }

    public function test_collection_model_can_be_created(): void
    {
        $collection = Collection::factory()->create([
            'name' => 'Test Collection',
            'slug' => 'test-collection',
            'description' => 'Test description',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals('Test Collection', $collection->name);
        $this->assertEquals('test-collection', $collection->slug);
        $this->assertTrue($collection->is_active);
    }

    public function test_collection_model_relationships(): void
    {
        $collection = Collection::factory()->create();
        $products = Product::factory()->count(3)->create();

        $collection->products()->attach($products->pluck('id'));

        $this->assertCount(3, $collection->products);
        $this->assertInstanceOf(Product::class, $collection->products->first());
    }

    public function test_collection_model_scopes(): void
    {
        $activeCollection = Collection::factory()->create(['is_active' => true]);
        $inactiveCollection = Collection::factory()->create(['is_active' => false]);

        $activeCollections = Collection::active()->get();
        $this->assertTrue($activeCollections->contains($activeCollection));
        $this->assertFalse($activeCollections->contains($inactiveCollection));
    }

    public function test_collection_model_accessors(): void
    {
        $collection = Collection::factory()->create();
        $products = Product::factory()->count(5)->create();
        $collection->products()->attach($products->pluck('id'));

        $this->assertIsInt($collection->products_count);
        $this->assertEquals(5, $collection->products_count);
    }

    public function test_collection_model_translation_methods(): void
    {
        $collection = Collection::factory()->create([
            'name' => 'Test Collection',
            'description' => 'Test description',
        ]);

        $this->assertIsString($collection->getTranslatedName());
        $this->assertIsString($collection->getTranslatedDescription());
        $this->assertIsString($collection->getTranslatedSlug());
    }

    public function test_collection_model_helper_methods(): void
    {
        $collection = Collection::factory()->create([
            'is_automatic' => false,
        ]);

        $this->assertTrue($collection->isManual());
        $this->assertFalse($collection->isAutomatic());
    }

    public function test_collection_model_info_methods(): void
    {
        $collection = Collection::factory()->create();

        $this->assertIsArray($collection->getCollectionInfo());
        $this->assertIsArray($collection->getSeoInfo());
        $this->assertIsArray($collection->getBusinessInfo());
        $this->assertIsArray($collection->getCompleteInfo());
    }

    public function test_collection_model_media_methods(): void
    {
        $collection = Collection::factory()->create();

        $this->assertNull($collection->getImageUrl());
        $this->assertNull($collection->getBannerUrl());
    }

    public function test_collection_model_cache_methods(): void
    {
        $collection = Collection::factory()->create();

        // Test that cache methods don't throw errors
        $this->assertNull(Collection::flushCaches());
    }

    public function test_collection_model_translation_management(): void
    {
        $collection = Collection::factory()->create();

        $this->assertIsArray($collection->getAvailableLocales());
        $this->assertFalse($collection->hasTranslationFor('lt'));
    }

    public function test_collection_resource_pages_exist(): void
    {
        $pages = CollectionResource::getPages();

        $this->assertIsArray($pages);
        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('view', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_collection_resource_relations(): void
    {
        $relations = CollectionResource::getRelations();

        $this->assertIsArray($relations);
    }

    public function test_collection_model_fillable_attributes(): void
    {
        $collection = new Collection;
        $fillable = $collection->getFillable();

        $this->assertIsArray($fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_collection_model_casts(): void
    {
        $collection = Collection::factory()->create([
            'is_active' => true,
            'is_automatic' => false,
            'sort_order' => 5,
        ]);

        $this->assertIsBool($collection->is_active);
        $this->assertIsBool($collection->is_automatic);
        $this->assertIsInt($collection->sort_order);
    }

    public function test_collection_model_soft_deletes(): void
    {
        $collection = Collection::factory()->create();
        $collectionId = $collection->id;

        $collection->delete();

        $this->assertSoftDeleted('collections', ['id' => $collectionId]);
    }

    public function test_collection_model_media_collections(): void
    {
        $collection = Collection::factory()->create();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertTrue(method_exists($collection, 'registerMediaCollections'));
    }

    public function test_collection_model_media_conversions(): void
    {
        $collection = Collection::factory()->create();

        $this->assertTrue(method_exists($collection, 'registerMediaConversions'));
    }

    public function test_collection_model_translatable_attributes(): void
    {
        $collection = new Collection;

        // Test that the model has translatable attributes defined
        $this->assertIsArray($collection::$translatable);
        $this->assertContains('name', $collection::$translatable);
        $this->assertContains('description', $collection::$translatable);
    }

    public function test_collection_model_route_key_name(): void
    {
        $collection = new Collection;

        $this->assertEquals('slug', $collection->getRouteKeyName());
    }

    public function test_collection_model_table_name(): void
    {
        $collection = new Collection;

        $this->assertEquals('collections', $collection->getTable());
    }

    public function test_collection_model_primary_key(): void
    {
        $collection = new Collection;

        $this->assertEquals('id', $collection->getKeyName());
    }

    public function test_collection_model_timestamps(): void
    {
        $collection = new Collection;

        $this->assertTrue($collection->usesTimestamps());
    }

    public function test_collection_model_soft_deletes_trait(): void
    {
        $collection = new Collection;

        $this->assertTrue(method_exists($collection, 'trashed'));
        $this->assertTrue(method_exists($collection, 'restore'));
        $this->assertTrue(method_exists($collection, 'forceDelete'));
    }

    public function test_collection_model_factory_trait(): void
    {
        $collection = new Collection;

        $this->assertTrue(method_exists($collection, 'factory'));
    }

    public function test_collection_model_media_trait(): void
    {
        $collection = new Collection;

        $this->assertTrue(method_exists($collection, 'addMedia'));
        $this->assertTrue(method_exists($collection, 'getFirstMediaUrl'));
    }

    public function test_collection_model_translations_trait(): void
    {
        $collection = new Collection;

        $this->assertTrue(method_exists($collection, 'trans'));
        $this->assertTrue(method_exists($collection, 'translations'));
    }
}
