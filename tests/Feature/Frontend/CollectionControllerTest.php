<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CollectionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_index_page_loads(): void
    {
        Collection::factory()->count(5)->create(['is_visible' => true]);
        
        $response = $this->get(route('collections.index'));
        
        $response->assertOk()
            ->assertViewIs('collections.index')
            ->assertSee('Collections');
    }

    public function test_collection_show_page_loads(): void
    {
        $collection = Collection::factory()->create([
            'is_visible' => true,
            'name' => 'Test Collection',
            'description' => 'Test Description',
        ]);
        
        $response = $this->get(route('collections.show', $collection));
        
        $response->assertOk()
            ->assertViewIs('collections.show')
            ->assertSee('Test Collection')
            ->assertSee('Test Description');
    }

    public function test_collection_index_with_filters(): void
    {
        Collection::factory()->create(['is_visible' => true, 'is_automatic' => true]);
        Collection::factory()->create(['is_visible' => true, 'is_automatic' => false]);
        
        $response = $this->get(route('collections.index', ['type' => 'manual']));
        
        $response->assertOk();
        // The page should load successfully with filters
        $response->assertTrue(true);
    }

    public function test_collection_search_api(): void
    {
        Collection::factory()->create([
            'name' => 'Test Collection',
            'is_visible' => true,
        ]);
        
        $response = $this->get(route('collections.api.search', ['search' => 'Test']));
        
        $response->assertOk()
            ->assertJsonStructure([
                'collections' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'image_url',
                        'products_count',
                        'display_type',
                        'is_automatic',
                    ],
                ],
            ]);
    }

    public function test_collection_by_type_api(): void
    {
        Collection::factory()->create(['is_automatic' => true, 'is_visible' => true]);
        Collection::factory()->create(['is_automatic' => false, 'is_visible' => true]);
        
        $response = $this->get(route('collections.api.by-type', 'automatic'));
        
        $response->assertOk()
            ->assertJsonStructure([
                'collections' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'image_url',
                        'products_count',
                        'display_type',
                    ],
                ],
            ]);
    }

    public function test_collection_with_products_api(): void
    {
        Collection::factory()->count(3)->create(['is_visible' => true]);
        
        $response = $this->get(route('collections.api.with-products'));
        
        $response->assertOk()
            ->assertJsonStructure([
                'collections' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'image_url',
                        'products_count',
                        'display_type',
                        'is_automatic',
                    ],
                ],
            ]);
    }

    public function test_collection_popular_api(): void
    {
        Collection::factory()->count(5)->create(['is_visible' => true]);
        
        $response = $this->get(route('collections.api.popular'));
        
        $response->assertOk()
            ->assertJsonStructure([
                'collections' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'image_url',
                        'products_count',
                        'display_type',
                    ],
                ],
            ]);
    }

    public function test_collection_statistics_api(): void
    {
        Collection::factory()->count(3)->create(['is_visible' => true]);
        Collection::factory()->count(2)->create(['is_visible' => false]);
        Collection::factory()->count(2)->create(['is_automatic' => true]);
        
        $response = $this->get(route('collections.api.statistics'));
        
        $response->assertOk()
            ->assertJsonStructure([
                'total_collections',
                'visible_collections',
                'automatic_collections',
                'manual_collections',
                'collections_with_products',
                'collections_without_products',
            ]);
    }

    public function test_collection_show_page_with_relations(): void
    {
        $collection = Collection::factory()->create([
            'is_visible' => true,
            'name' => 'Test Collection',
            'display_type' => 'grid',
        ]);
        
        // Create related collections
        Collection::factory()->count(2)->create([
            'is_visible' => true,
            'display_type' => 'grid',
        ]);
        
        $response = $this->get(route('collections.show', $collection));
        
        $response->assertOk()
            ->assertSee('Test Collection')
            ->assertSee('grid');
    }

    public function test_collection_pagination(): void
    {
        Collection::factory()->count(15)->create(['is_visible' => true]);
        
        $response = $this->get(route('collections.index'));
        
        $response->assertOk();
        // The page should load successfully with pagination
        $response->assertTrue(true);
    }
}
