<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CollectionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_collection_resource_list_page_renders(): void
    {
        Collection::factory()->count(3)->create();

        $response = $this->get(route('filament.admin.resources.collections.index'));

        $response->assertOk();
    }

    public function test_collection_resource_create_page_renders(): void
    {
        $response = $this->get(route('filament.admin.resources.collections.create'));

        $response->assertOk();
    }

    public function test_collection_resource_can_create_collection(): void
    {
        $collectionData = [
            'name' => 'Test Collection',
            'slug' => 'test-collection',
            'description' => 'Test description',
            'is_visible' => true,
            'is_automatic' => false,
            'sort_order' => 1,
            'display_type' => 'grid',
            'products_per_page' => 12,
            'show_filters' => true,
        ];

        $response = $this->post(route('filament.admin.resources.collections.store'), $collectionData);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('collections', [
            'name' => 'Test Collection',
            'slug' => 'test-collection',
            'description' => 'Test description',
            'is_visible' => true,
            'is_automatic' => false,
            'sort_order' => 1,
            'display_type' => 'grid',
            'products_per_page' => 12,
            'show_filters' => true,
        ]);
    }

    public function test_collection_resource_can_edit_collection(): void
    {
        $collection = Collection::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original Description',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ];

        $response = $this->put(route('filament.admin.resources.collections.update', $collection), $updateData);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ]);
    }

    public function test_collection_resource_can_delete_collection(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->delete(route('filament.admin.resources.collections.destroy', $collection));

        $response->assertRedirect();
        
        $this->assertSoftDeleted('collections', [
            'id' => $collection->id,
        ]);
    }

    public function test_collection_resource_widgets_are_included(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->get(route('filament.admin.resources.collections.index'));

        $response->assertOk();
        // Widgets should be rendered on the index page
        $response->assertSee('Collection Statistics');
    }
}
