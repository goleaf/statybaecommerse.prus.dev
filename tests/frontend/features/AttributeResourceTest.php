<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Attribute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class AttributeResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->admin()->create();
        Permission::findOrCreate('view notifications', 'web');
        $user->givePermissionTo('view notifications');
        $this->actingAs($user);
    }

    public function test_attribute_resource_list_page_renders(): void
    {
        Attribute::factory()->count(3)->create();

        $response = $this->get(route('filament.admin.resources.attributes.index'));

        $response->assertOk();
    }

    public function test_attribute_resource_create_page_renders(): void
    {
        $response = $this->get(route('filament.admin.resources.attributes.create'));

        $response->assertOk();
    }

    public function test_attribute_resource_can_create_attribute(): void
    {
        $attributeData = [
            'name' => 'Test Attribute',
            'slug' => 'test-attribute',
            'type' => 'text',
            'description' => 'Test description',
            'is_required' => true,
            'is_filterable' => true,
            'is_searchable' => false,
            'is_visible' => true,
            'is_enabled' => true,
            'sort_order' => 1,
            'group_name' => 'test-group',
        ];

        $response = $this->post(route('filament.admin.resources.attributes.store'), $attributeData);

        $response->assertRedirect();

        $this->assertDatabaseHas('attributes', [
            'name' => 'Test Attribute',
            'slug' => 'test-attribute',
            'type' => 'text',
            'description' => 'Test description',
            'is_required' => true,
            'is_filterable' => true,
            'is_searchable' => false,
            'is_visible' => true,
            'is_enabled' => true,
            'sort_order' => 1,
            'group_name' => 'test-group',
        ]);
    }

    public function test_attribute_resource_can_edit_attribute(): void
    {
        $attribute = Attribute::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original Description',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ];

        $response = $this->put(route('filament.admin.resources.attributes.update', $attribute), $updateData);

        $response->assertRedirect();

        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ]);
    }

    public function test_attribute_resource_can_delete_attribute(): void
    {
        $attribute = Attribute::factory()->create();

        $response = $this->delete(route('filament.admin.resources.attributes.destroy', $attribute));

        $response->assertRedirect();

        $this->assertSoftDeleted('attributes', [
            'id' => $attribute->id,
        ]);
    }

    public function test_attribute_resource_widgets_are_included(): void
    {
        $attribute = Attribute::factory()->create();

        $response = $this->get(route('filament.admin.resources.attributes.index'));

        $response->assertOk();
        // Widgets should be rendered on the index page
        $response->assertSee('Attribute Statistics');
    }
}
