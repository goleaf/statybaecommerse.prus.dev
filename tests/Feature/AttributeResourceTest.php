<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\AttributeResource\Pages\CreateAttribute;
use App\Filament\Resources\AttributeResource\Pages\EditAttribute;
use App\Filament\Resources\AttributeResource\Pages\ListAttributes;
use App\Filament\Resources\AttributeResource\Pages\ViewAttribute;
use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class AttributeResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user for authentication
        $this->adminUser = \App\Models\User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_attributes(): void
    {
        // Arrange
        $attributes = Attribute::factory()->count(5)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->assertCanSeeTableRecords($attributes);
    }

    public function test_can_create_attribute(): void
    {
        // Arrange
        $attributeData = [
            'name' => 'Test Attribute',
            'slug' => 'test-attribute',
            'description' => 'Test attribute description',
            'type' => 'text',
            'input_type' => 'text',
            'is_required' => true,
            'is_filterable' => true,
            'is_searchable' => true,
            'is_active' => true,
            'group_name' => 'general',
            'sort_order' => 1,
            'options' => [],
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAttribute::class)
            ->fillForm($attributeData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('attributes', [
            'name' => 'Test Attribute',
            'slug' => 'test-attribute',
            'description' => 'Test attribute description',
            'type' => 'text',
        ]);
    }

    public function test_can_edit_attribute(): void
    {
        // Arrange
        $attribute = Attribute::factory()->create();
        $newName = 'Updated Attribute';

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditAttribute::class, ['record' => $attribute->id])
            ->fillForm(['name' => $newName])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'name' => $newName,
        ]);
    }

    public function test_can_view_attribute(): void
    {
        // Arrange
        $attribute = Attribute::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ViewAttribute::class, ['record' => $attribute->id])
            ->assertSee($attribute->name);
    }

    public function test_can_filter_attributes_by_type(): void
    {
        // Arrange
        $textAttributes = Attribute::factory()->count(3)->create(['type' => 'text']);
        $selectAttributes = Attribute::factory()->count(2)->create(['type' => 'select']);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->filterTable('type', 'text')
            ->assertCanSeeTableRecords($textAttributes)
            ->assertCanNotSeeTableRecords($selectAttributes);
    }

    public function test_can_filter_attributes_by_group(): void
    {
        // Arrange
        $generalAttributes = Attribute::factory()->count(3)->create(['group_name' => 'general']);
        $technicalAttributes = Attribute::factory()->count(2)->create(['group_name' => 'technical']);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->filterTable('group_name', 'general')
            ->assertCanSeeTableRecords($generalAttributes)
            ->assertCanNotSeeTableRecords($technicalAttributes);
    }

    public function test_can_filter_attributes_by_required_status(): void
    {
        // Arrange
        $requiredAttributes = Attribute::factory()->count(3)->create(['is_required' => true]);
        $optionalAttributes = Attribute::factory()->count(2)->create(['is_required' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->filterTable('is_required', '1')
            ->assertCanSeeTableRecords($requiredAttributes)
            ->assertCanNotSeeTableRecords($optionalAttributes);
    }

    public function test_can_filter_attributes_by_active_status(): void
    {
        // Arrange
        $activeAttributes = Attribute::factory()->count(3)->create(['is_active' => true]);
        $inactiveAttributes = Attribute::factory()->count(2)->create(['is_active' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->filterTable('is_active', '1')
            ->assertCanSeeTableRecords($activeAttributes)
            ->assertCanNotSeeTableRecords($inactiveAttributes);
    }

    public function test_can_toggle_attribute_active_status(): void
    {
        // Arrange
        $attribute = Attribute::factory()->create(['is_active' => true]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->callTableAction('toggle_active', $attribute)
            ->assertNotified();

        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'is_active' => false,
        ]);
    }

    public function test_can_bulk_activate_attributes(): void
    {
        // Arrange
        $attributes = Attribute::factory()->count(3)->create(['is_active' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->callTableBulkAction('activate', $attributes)
            ->assertNotified();

        foreach ($attributes as $attribute) {
            $this->assertDatabaseHas('attributes', [
                'id' => $attribute->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_attributes(): void
    {
        // Arrange
        $attributes = Attribute::factory()->count(3)->create(['is_active' => true]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->callTableBulkAction('deactivate', $attributes)
            ->assertNotified();

        foreach ($attributes as $attribute) {
            $this->assertDatabaseHas('attributes', [
                'id' => $attribute->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_can_search_attributes(): void
    {
        // Arrange
        $searchableAttribute = Attribute::factory()->create(['name' => 'Unique Attribute']);
        $otherAttributes = Attribute::factory()->count(3)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->searchTable('Unique Attribute')
            ->assertCanSeeTableRecords([$searchableAttribute])
            ->assertCanNotSeeTableRecords($otherAttributes);
    }

    public function test_can_sort_attributes_by_sort_order(): void
    {
        // Arrange
        $attribute1 = Attribute::factory()->create(['sort_order' => 2]);
        $attribute2 = Attribute::factory()->create(['sort_order' => 1]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->sortTable('sort_order', 'asc')
            ->assertCanSeeTableRecords([$attribute2, $attribute1], inOrder: true);
    }

    public function test_validates_required_fields_on_create(): void
    {
        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAttribute::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    public function test_validates_slug_uniqueness_on_create(): void
    {
        // Arrange
        $existingAttribute = Attribute::factory()->create(['slug' => 'existing-slug']);
        $attributeData = [
            'name' => 'Test Attribute',
            'slug' => 'existing-slug',
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAttribute::class)
            ->fillForm($attributeData)
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_validates_slug_format(): void
    {
        // Arrange
        $attributeData = [
            'name' => 'Test Attribute',
            'slug' => 'invalid slug with spaces',
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAttribute::class)
            ->fillForm($attributeData)
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_can_create_attribute_with_options(): void
    {
        // Arrange
        $attributeData = [
            'name' => 'Test Select Attribute',
            'type' => 'select',
            'options' => [
                [
                    'value' => 'option1',
                    'label' => 'Option 1',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'value' => 'option2',
                    'label' => 'Option 2',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
            ],
        ];

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(CreateAttribute::class)
            ->fillForm($attributeData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('attributes', [
            'name' => 'Test Select Attribute',
            'type' => 'select',
        ]);
    }

    public function test_can_delete_attribute(): void
    {
        // Arrange
        $attribute = Attribute::factory()->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->callTableAction('delete', $attribute)
            ->assertNotified();

        $this->assertDatabaseMissing('attributes', ['id' => $attribute->id]);
    }

    public function test_can_bulk_delete_attributes(): void
    {
        // Arrange
        $attributes = Attribute::factory()->count(3)->create();

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->callTableBulkAction('delete', $attributes)
            ->assertNotified();

        foreach ($attributes as $attribute) {
            $this->assertDatabaseMissing('attributes', ['id' => $attribute->id]);
        }
    }

    public function test_can_filter_attributes_by_filterable_status(): void
    {
        // Arrange
        $filterableAttributes = Attribute::factory()->count(3)->create(['is_filterable' => true]);
        $nonFilterableAttributes = Attribute::factory()->count(2)->create(['is_filterable' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->filterTable('is_filterable', '1')
            ->assertCanSeeTableRecords($filterableAttributes)
            ->assertCanNotSeeTableRecords($nonFilterableAttributes);
    }

    public function test_can_filter_attributes_by_searchable_status(): void
    {
        // Arrange
        $searchableAttributes = Attribute::factory()->count(3)->create(['is_searchable' => true]);
        $nonSearchableAttributes = Attribute::factory()->count(2)->create(['is_searchable' => false]);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(ListAttributes::class)
            ->filterTable('is_searchable', '1')
            ->assertCanSeeTableRecords($searchableAttributes)
            ->assertCanNotSeeTableRecords($nonSearchableAttributes);
    }

    public function test_can_set_validation_rules(): void
    {
        // Arrange
        $attribute = Attribute::factory()->create();
        $validationRules = 'required|string|max:255';

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditAttribute::class, ['record' => $attribute->id])
            ->fillForm(['validation_rules' => $validationRules])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'validation_rules' => $validationRules,
        ]);
    }

    public function test_can_set_min_max_length_for_text_attributes(): void
    {
        // Arrange
        $attribute = Attribute::factory()->create(['type' => 'text']);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditAttribute::class, ['record' => $attribute->id])
            ->fillForm([
                'min_length' => 5,
                'max_length' => 100,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'min_length' => 5,
            'max_length' => 100,
        ]);
    }

    public function test_can_set_min_max_value_for_number_attributes(): void
    {
        // Arrange
        $attribute = Attribute::factory()->create(['type' => 'number']);

        // Act & Assert
        Livewire::actingAs($this->adminUser)
            ->test(EditAttribute::class, ['record' => $attribute->id])
            ->fillForm([
                'min_value' => 0,
                'max_value' => 1000,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'min_value' => 0,
            'max_value' => 1000,
        ]);
    }
}
