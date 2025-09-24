<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\VariantAttributeValueResource;
use App\Models\Attribute;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantAttributeValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VariantAttributeValueResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($this->user);
    }

    public function test_can_list_variant_attribute_values(): void
    {
        // Create test data
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();
        $variantAttributeValue = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
        ]);

        // Test the list page
        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->assertCanSeeTableRecords([$variantAttributeValue])
            ->assertCanRenderTableColumn('variant.name')
            ->assertCanRenderTableColumn('attribute.name')
            ->assertCanRenderTableColumn('attribute_value')
            ->assertCanRenderTableColumn('is_filterable')
            ->assertCanRenderTableColumn('is_searchable');
    }

    public function test_can_create_variant_attribute_value(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();

        Livewire::test(VariantAttributeValueResource\Pages\CreateVariantAttributeValue::class)
            ->fillForm([
                'variant_id' => $variant->id,
                'attribute_id' => $attribute->id,
                'attribute_value' => 'Test Value',
                'attribute_value_display' => 'Test Display Value',
                'attribute_value_lt' => 'Test Value LT',
                'attribute_value_en' => 'Test Value EN',
                'attribute_value_slug' => 'test-value',
                'sort_order' => 1,
                'is_filterable' => true,
                'is_searchable' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('variant_attribute_values', [
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'attribute_value' => 'Test Value',
            'attribute_value_display' => 'Test Display Value',
            'attribute_value_lt' => 'Test Value LT',
            'attribute_value_en' => 'Test Value EN',
            'attribute_value_slug' => 'test-value',
            'sort_order' => 1,
            'is_filterable' => true,
            'is_searchable' => true,
        ]);
    }

    public function test_can_edit_variant_attribute_value(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();
        $variantAttributeValue = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\EditVariantAttributeValue::class, [
            'record' => $variantAttributeValue->getRouteKey(),
        ])
            ->fillForm([
                'attribute_value' => 'Updated Value',
                'attribute_value_display' => 'Updated Display Value',
                'sort_order' => 5,
                'is_filterable' => false,
                'is_searchable' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('variant_attribute_values', [
            'id' => $variantAttributeValue->id,
            'attribute_value' => 'Updated Value',
            'attribute_value_display' => 'Updated Display Value',
            'sort_order' => 5,
            'is_filterable' => false,
            'is_searchable' => false,
        ]);
    }

    public function test_can_view_variant_attribute_value(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();
        $variantAttributeValue = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ViewVariantAttributeValue::class, [
            'record' => $variantAttributeValue->getRouteKey(),
        ])
            ->assertCanSeeTableColumn('variant.name')
            ->assertCanSeeTableColumn('attribute.name')
            ->assertCanSeeTableColumn('attribute_value')
            ->assertCanSeeTableColumn('is_filterable')
            ->assertCanSeeTableColumn('is_searchable');
    }

    public function test_can_delete_variant_attribute_value(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();
        $variantAttributeValue = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->callTableAction('delete', $variantAttributeValue);

        $this->assertDatabaseMissing('variant_attribute_values', [
            'id' => $variantAttributeValue->id,
        ]);
    }

    public function test_can_toggle_filterable_status(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();
        $variantAttributeValue = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'is_filterable' => true,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->callTableAction('toggle_filterable', $variantAttributeValue);

        $this->assertDatabaseHas('variant_attribute_values', [
            'id' => $variantAttributeValue->id,
            'is_filterable' => false,
        ]);
    }

    public function test_can_toggle_searchable_status(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();
        $variantAttributeValue = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'is_searchable' => true,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->callTableAction('toggle_searchable', $variantAttributeValue);

        $this->assertDatabaseHas('variant_attribute_values', [
            'id' => $variantAttributeValue->id,
            'is_searchable' => false,
        ]);
    }

    public function test_can_duplicate_variant_attribute_value(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();
        $variantAttributeValue = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'attribute_value' => 'Original Value',
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->callTableAction('duplicate', $variantAttributeValue);

        $this->assertDatabaseHas('variant_attribute_values', [
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'attribute_value' => 'Original Value (Copy)',
        ]);
    }

    public function test_can_filter_by_variant(): void
    {
        $variant1 = ProductVariant::factory()->create(['name' => 'Variant 1']);
        $variant2 = ProductVariant::factory()->create(['name' => 'Variant 2']);
        $attribute = Attribute::factory()->create();

        $variantAttributeValue1 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant1->id,
            'attribute_id' => $attribute->id,
        ]);
        $variantAttributeValue2 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant2->id,
            'attribute_id' => $attribute->id,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->filterTable('variant_id', $variant1->id)
            ->assertCanSeeTableRecords([$variantAttributeValue1])
            ->assertCanNotSeeTableRecords([$variantAttributeValue2]);
    }

    public function test_can_filter_by_attribute(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute1 = Attribute::factory()->create(['name' => 'Attribute 1']);
        $attribute2 = Attribute::factory()->create(['name' => 'Attribute 2']);

        $variantAttributeValue1 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute1->id,
        ]);
        $variantAttributeValue2 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute2->id,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->filterTable('attribute_id', $attribute1->id)
            ->assertCanSeeTableRecords([$variantAttributeValue1])
            ->assertCanNotSeeTableRecords([$variantAttributeValue2]);
    }

    public function test_can_filter_by_filterable_status(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();

        $filterableValue = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'is_filterable' => true,
        ]);
        $nonFilterableValue = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'is_filterable' => false,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->filterTable('is_filterable', true)
            ->assertCanSeeTableRecords([$filterableValue])
            ->assertCanNotSeeTableRecords([$nonFilterableValue]);
    }

    public function test_can_filter_by_searchable_status(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();

        $searchableValue = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'is_searchable' => true,
        ]);
        $nonSearchableValue = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'is_searchable' => false,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->filterTable('is_searchable', true)
            ->assertCanSeeTableRecords([$searchableValue])
            ->assertCanNotSeeTableRecords([$nonSearchableValue]);
    }

    public function test_can_bulk_make_filterable(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();

        $variantAttributeValue1 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'is_filterable' => false,
        ]);
        $variantAttributeValue2 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'is_filterable' => false,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->callTableBulkAction('make_filterable', [$variantAttributeValue1, $variantAttributeValue2]);

        $this->assertDatabaseHas('variant_attribute_values', [
            'id' => $variantAttributeValue1->id,
            'is_filterable' => true,
        ]);
        $this->assertDatabaseHas('variant_attribute_values', [
            'id' => $variantAttributeValue2->id,
            'is_filterable' => true,
        ]);
    }

    public function test_can_bulk_make_searchable(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();

        $variantAttributeValue1 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'is_searchable' => false,
        ]);
        $variantAttributeValue2 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'is_searchable' => false,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->callTableBulkAction('make_searchable', [$variantAttributeValue1, $variantAttributeValue2]);

        $this->assertDatabaseHas('variant_attribute_values', [
            'id' => $variantAttributeValue1->id,
            'is_searchable' => true,
        ]);
        $this->assertDatabaseHas('variant_attribute_values', [
            'id' => $variantAttributeValue2->id,
            'is_searchable' => true,
        ]);
    }

    public function test_can_bulk_update_sort_order(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();

        $variantAttributeValue1 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'sort_order' => 1,
        ]);
        $variantAttributeValue2 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'sort_order' => 2,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->callTableBulkAction('update_sort_order', [$variantAttributeValue1, $variantAttributeValue2], [
                'sort_order' => 10,
            ]);

        $this->assertDatabaseHas('variant_attribute_values', [
            'id' => $variantAttributeValue1->id,
            'sort_order' => 10,
        ]);
        $this->assertDatabaseHas('variant_attribute_values', [
            'id' => $variantAttributeValue2->id,
            'sort_order' => 10,
        ]);
    }

    public function test_can_search_variant_attribute_values(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();

        $variantAttributeValue1 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'attribute_value' => 'Searchable Value',
        ]);
        $variantAttributeValue2 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'attribute_value' => 'Different Value',
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->searchTable('Searchable')
            ->assertCanSeeTableRecords([$variantAttributeValue1])
            ->assertCanNotSeeTableRecords([$variantAttributeValue2]);
    }

    public function test_can_sort_by_sort_order(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute = Attribute::factory()->create();

        $variantAttributeValue1 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'sort_order' => 3,
        ]);
        $variantAttributeValue2 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'sort_order' => 1,
        ]);
        $variantAttributeValue3 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'sort_order' => 2,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->sortTable('sort_order')
            ->assertCanSeeTableRecords([$variantAttributeValue2, $variantAttributeValue3, $variantAttributeValue1]);
    }

    public function test_form_validation_works(): void
    {
        Livewire::test(VariantAttributeValueResource\Pages\CreateVariantAttributeValue::class)
            ->fillForm([
                'variant_id' => null,
                'attribute_id' => null,
                'attribute_value' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['variant_id', 'attribute_id', 'attribute_value']);
    }

    public function test_can_group_by_variant(): void
    {
        $variant1 = ProductVariant::factory()->create(['name' => 'Variant 1']);
        $variant2 = ProductVariant::factory()->create(['name' => 'Variant 2']);
        $attribute = Attribute::factory()->create();

        $variantAttributeValue1 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant1->id,
            'attribute_id' => $attribute->id,
        ]);
        $variantAttributeValue2 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant2->id,
            'attribute_id' => $attribute->id,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->groupTable('variant.name')
            ->assertCanSeeTableRecords([$variantAttributeValue1, $variantAttributeValue2]);
    }

    public function test_can_group_by_attribute(): void
    {
        $variant = ProductVariant::factory()->create();
        $attribute1 = Attribute::factory()->create(['name' => 'Attribute 1']);
        $attribute2 = Attribute::factory()->create(['name' => 'Attribute 2']);

        $variantAttributeValue1 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute1->id,
        ]);
        $variantAttributeValue2 = VariantAttributeValue::factory()->create([
            'variant_id' => $variant->id,
            'attribute_id' => $attribute2->id,
        ]);

        Livewire::test(VariantAttributeValueResource\Pages\ListVariantAttributeValues::class)
            ->groupTable('attribute.name')
            ->assertCanSeeTableRecords([$variantAttributeValue1, $variantAttributeValue2]);
    }
}
