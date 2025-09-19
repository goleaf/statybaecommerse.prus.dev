<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CollectionFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_factory_creates_valid_collection(): void
    {
        $collection = Collection::factory()->create();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertDatabaseHas('collections', ['id' => $collection->id]);
    }

    public function test_collection_factory_with_custom_attributes(): void
    {
        $collection = Collection::factory()->create([
            'name' => 'Custom Collection',
            'slug' => 'custom-collection',
            'description' => 'Custom description',
            'is_visible' => true,
            'is_automatic' => false,
            'sort_order' => 5,
        ]);

        $this->assertEquals('Custom Collection', $collection->name);
        $this->assertEquals('custom-collection', $collection->slug);
        $this->assertEquals('Custom description', $collection->description);
        $this->assertTrue($collection->is_visible);
        $this->assertFalse($collection->is_automatic);
        $this->assertEquals(5, $collection->sort_order);
    }

    public function test_collection_factory_visible_state(): void
    {
        $visibleCollection = Collection::factory()->visible()->create();
        $hiddenCollection = Collection::factory()->hidden()->create();

        $this->assertTrue($visibleCollection->is_visible);
        $this->assertFalse($hiddenCollection->is_visible);
    }

    public function test_collection_factory_automatic_state(): void
    {
        $automaticCollection = Collection::factory()->automatic()->create();
        $manualCollection = Collection::factory()->manual()->create();

        $this->assertTrue($automaticCollection->is_automatic);
        $this->assertFalse($manualCollection->is_automatic);
    }

    public function test_collection_factory_active_state(): void
    {
        $activeCollection = Collection::factory()->active()->create();
        $inactiveCollection = Collection::factory()->inactive()->create();

        $this->assertTrue($activeCollection->is_active);
        $this->assertFalse($inactiveCollection->is_active);
    }

    public function test_collection_factory_with_products(): void
    {
        $collection = Collection::factory()->withProducts(3)->create();

        $this->assertCount(3, $collection->products);
        $this->assertEquals(3, $collection->products_count);
    }

    public function test_collection_factory_with_translations(): void
    {
        $collection = Collection::factory()->withTranslations()->create();

        $this->assertTrue($collection->relationLoaded('translations'));
        $this->assertGreaterThan(0, $collection->translations->count());
    }

    public function test_collection_factory_display_types(): void
    {
        $gridCollection = Collection::factory()->grid()->create();
        $listCollection = Collection::factory()->list()->create();
        $carouselCollection = Collection::factory()->carousel()->create();

        $this->assertEquals('grid', $gridCollection->display_type);
        $this->assertEquals('list', $listCollection->display_type);
        $this->assertEquals('carousel', $carouselCollection->display_type);
    }

    public function test_collection_factory_with_seo_data(): void
    {
        $collection = Collection::factory()->withSeo()->create();

        $this->assertNotNull($collection->seo_title);
        $this->assertNotNull($collection->seo_description);
        $this->assertNotNull($collection->meta_title);
        $this->assertNotNull($collection->meta_description);
        $this->assertIsArray($collection->meta_keywords);
    }

    public function test_collection_factory_with_rules(): void
    {
        $rules = [
            'category' => 'tools',
            'brand' => 'bosch',
            'price_min' => 100,
        ];

        $collection = Collection::factory()->withRules($rules)->create();

        $this->assertEquals($rules, $collection->rules);
    }

    public function test_collection_factory_creates_unique_slugs(): void
    {
        $collection1 = Collection::factory()->create(['name' => 'Test Collection']);
        $collection2 = Collection::factory()->create(['name' => 'Test Collection']);

        $this->assertNotEquals($collection1->slug, $collection2->slug);
    }

    public function test_collection_factory_generates_valid_slugs(): void
    {
        $collection = Collection::factory()->create(['name' => 'Test Collection with Special Characters!']);

        $this->assertStringNotContainsString('!', $collection->slug);
        $this->assertStringNotContainsString(' ', $collection->slug);
        $this->assertStringContainsString('test-collection', $collection->slug);
    }

    public function test_collection_factory_with_media(): void
    {
        $collection = Collection::factory()->withMedia()->create();

        $this->assertInstanceOf(\Spatie\MediaLibrary\HasMedia::class, $collection);
    }

    public function test_collection_factory_with_specific_sort_order(): void
    {
        $collection = Collection::factory()->create(['sort_order' => 10]);

        $this->assertEquals(10, $collection->sort_order);
    }

    public function test_collection_factory_with_specific_products_per_page(): void
    {
        $collection = Collection::factory()->create(['products_per_page' => 20]);

        $this->assertEquals(20, $collection->products_per_page);
    }

    public function test_collection_factory_with_show_filters(): void
    {
        $collectionWithFilters = Collection::factory()->withFilters()->create();
        $collectionWithoutFilters = Collection::factory()->withoutFilters()->create();

        $this->assertTrue($collectionWithFilters->show_filters);
        $this->assertFalse($collectionWithoutFilters->show_filters);
    }

    public function test_collection_factory_creates_multiple_collections(): void
    {
        $collections = Collection::factory()->count(5)->create();

        $this->assertCount(5, $collections);
        $this->assertDatabaseCount('collections', 5);
    }

    public function test_collection_factory_with_sequence(): void
    {
        $collections = Collection::factory()
            ->count(3)
            ->sequence(
                ['name' => 'First Collection'],
                ['name' => 'Second Collection'],
                ['name' => 'Third Collection'],
            )
            ->create();

        $this->assertEquals('First Collection', $collections[0]->name);
        $this->assertEquals('Second Collection', $collections[1]->name);
        $this->assertEquals('Third Collection', $collections[2]->name);
    }

    public function test_collection_factory_after_creating_callback(): void
    {
        $collection = Collection::factory()
            ->afterCreating(function (Collection $collection) {
                $collection->update(['name' => 'Modified Name']);
            })
            ->create();

        $this->assertEquals('Modified Name', $collection->name);
    }

    public function test_collection_factory_after_making_callback(): void
    {
        $collection = Collection::factory()
            ->afterMaking(function (Collection $collection) {
                $collection->name = 'Made Name';
            })
            ->create();

        $this->assertEquals('Made Name', $collection->name);
    }

    public function test_collection_factory_with_trashed(): void
    {
        $collection = Collection::factory()->trashed()->create();

        $this->assertSoftDeleted('collections', ['id' => $collection->id]);
    }

    public function test_collection_factory_with_specific_locale(): void
    {
        app()->setLocale('lt');
        
        $collection = Collection::factory()->create();

        $this->assertNotNull($collection->name);
        $this->assertNotNull($collection->slug);
    }

    public function test_collection_factory_with_custom_meta_keywords(): void
    {
        $keywords = ['keyword1', 'keyword2', 'keyword3'];
        $collection = Collection::factory()->create(['meta_keywords' => $keywords]);

        $this->assertEquals($keywords, $collection->meta_keywords);
    }

    public function test_collection_factory_with_custom_rules(): void
    {
        $rules = [
            'category' => 'tools',
            'brand' => 'bosch',
            'price_min' => 100,
            'price_max' => 1000,
        ];

        $collection = Collection::factory()->create(['rules' => $rules]);

        $this->assertEquals($rules, $collection->rules);
    }

    public function test_collection_factory_with_max_products(): void
    {
        $collection = Collection::factory()->create(['max_products' => 50]);

        $this->assertEquals(50, $collection->max_products);
    }

    public function test_collection_factory_creates_with_default_values(): void
    {
        $collection = Collection::factory()->create();

        $this->assertNotNull($collection->name);
        $this->assertNotNull($collection->slug);
        $this->assertIsBool($collection->is_visible);
        $this->assertIsBool($collection->is_automatic);
        $this->assertIsBool($collection->is_active);
        $this->assertIsInt($collection->sort_order);
        $this->assertIsString($collection->display_type);
        $this->assertIsInt($collection->products_per_page);
        $this->assertIsBool($collection->show_filters);
    }
}
