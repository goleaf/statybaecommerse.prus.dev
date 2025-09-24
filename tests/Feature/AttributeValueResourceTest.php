<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttributeValueResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
        ]));
    }

    public function test_can_list_attribute_values(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->assertCanSeeTableRecords([$attributeValue]);
    }

    public function test_can_create_attribute_value(): void
    {
        $attribute = Attribute::factory()->create();

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\CreateAttributeValue::class)
            ->fillForm([
                'attribute_id' => $attribute->id,
                'value' => 'Test Value',
                'display_value' => 'Test Display Value',
                'description' => 'Test Description',
                'is_active' => true,
                'is_default' => false,
                'is_searchable' => true,
                'sort_order' => 1,
                'color_code' => '#FF0000',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'value' => 'Test Value',
            'display_value' => 'Test Display Value',
            'description' => 'Test Description',
            'is_active' => true,
            'is_default' => false,
            'is_searchable' => true,
            'sort_order' => 1,
            'color_code' => '#FF0000',
        ]);
    }

    public function test_can_edit_attribute_value(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\EditAttributeValue::class, [
            'record' => $attributeValue->id,
        ])
            ->fillForm([
                'value' => 'Updated Value',
                'display_value' => 'Updated Display Value',
                'description' => 'Updated Description',
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('attribute_values', [
            'id' => $attributeValue->id,
            'value' => 'Updated Value',
            'display_value' => 'Updated Display Value',
            'description' => 'Updated Description',
            'is_active' => false,
        ]);
    }

    public function test_can_view_attribute_value(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ViewAttributeValue::class, [
            'record' => $attributeValue->id,
        ])
            ->assertCanSeeTableRecords([$attributeValue]);
    }

    public function test_can_delete_attribute_value(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->callTableAction('delete', $attributeValue);

        $this->assertSoftDeleted('attribute_values', [
            'id' => $attributeValue->id,
        ]);
    }

    public function test_can_filter_attribute_values_by_attribute(): void
    {
        $attribute1 = Attribute::factory()->create(['name' => 'Color']);
        $attribute2 = Attribute::factory()->create(['name' => 'Size']);

        $attributeValue1 = AttributeValue::factory()->create([
            'attribute_id' => $attribute1->id,
            'value' => 'Red',
        ]);
        $attributeValue2 = AttributeValue::factory()->create([
            'attribute_id' => $attribute2->id,
            'value' => 'Large',
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->filterTable('attribute_id', $attribute1->id)
            ->assertCanSeeTableRecords([$attributeValue1])
            ->assertCanNotSeeTableRecords([$attributeValue2]);
    }

    public function test_can_filter_attribute_values_by_active_status(): void
    {
        $attribute = Attribute::factory()->create();

        $activeAttributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'is_active' => true,
        ]);
        $inactiveAttributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'is_active' => false,
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$activeAttributeValue])
            ->assertCanNotSeeTableRecords([$inactiveAttributeValue]);
    }

    public function test_can_bulk_activate_attribute_values(): void
    {
        $attribute = Attribute::factory()->create();

        $attributeValue1 = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'is_active' => false,
        ]);
        $attributeValue2 = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'is_active' => false,
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->callTableBulkAction('activate', [$attributeValue1, $attributeValue2]);

        $this->assertDatabaseHas('attribute_values', [
            'id' => $attributeValue1->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('attribute_values', [
            'id' => $attributeValue2->id,
            'is_active' => true,
        ]);
    }

    public function test_can_bulk_deactivate_attribute_values(): void
    {
        $attribute = Attribute::factory()->create();

        $attributeValue1 = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'is_active' => true,
        ]);
        $attributeValue2 = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->callTableBulkAction('deactivate', [$attributeValue1, $attributeValue2]);

        $this->assertDatabaseHas('attribute_values', [
            'id' => $attributeValue1->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('attribute_values', [
            'id' => $attributeValue2->id,
            'is_active' => false,
        ]);
    }

    public function test_can_set_default_attribute_value(): void
    {
        $attribute = Attribute::factory()->create();

        $attributeValue1 = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'is_default' => true,
        ]);
        $attributeValue2 = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'is_default' => false,
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->callTableAction('set_default', $attributeValue2);

        $this->assertDatabaseHas('attribute_values', [
            'id' => $attributeValue1->id,
            'is_default' => false,
        ]);
        $this->assertDatabaseHas('attribute_values', [
            'id' => $attributeValue2->id,
            'is_default' => true,
        ]);
    }

    public function test_can_duplicate_attribute_value(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Original Value',
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->callTableAction('duplicate', $attributeValue);

        $this->assertDatabaseHas('attribute_values', [
            'value' => 'Original Value (Copy)',
            'is_default' => false,
        ]);
    }

    public function test_can_search_attribute_values(): void
    {
        $attribute = Attribute::factory()->create();

        $attributeValue1 = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Red Color',
        ]);
        $attributeValue2 = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Blue Color',
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->searchTable('Red')
            ->assertCanSeeTableRecords([$attributeValue1])
            ->assertCanNotSeeTableRecords([$attributeValue2]);
    }

    public function test_can_sort_attribute_values(): void
    {
        $attribute = Attribute::factory()->create();

        $attributeValue1 = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'A Value',
            'sort_order' => 2,
        ]);
        $attributeValue2 = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'B Value',
            'sort_order' => 1,
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->sortTable('sort_order')
            ->assertCanSeeTableRecords([$attributeValue2, $attributeValue1]);
    }

    public function test_can_filter_by_has_description(): void
    {
        $attribute = Attribute::factory()->create();

        $attributeValueWithDescription = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'description' => 'Has description',
        ]);
        $attributeValueWithoutDescription = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'description' => null,
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->filterTable('has_description')
            ->assertCanSeeTableRecords([$attributeValueWithDescription])
            ->assertCanNotSeeTableRecords([$attributeValueWithoutDescription]);
    }

    public function test_can_filter_by_has_display_value(): void
    {
        $attribute = Attribute::factory()->create();

        $attributeValueWithDisplayValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'display_value' => 'Display Value',
        ]);
        $attributeValueWithoutDisplayValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'display_value' => null,
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->filterTable('has_display_value')
            ->assertCanSeeTableRecords([$attributeValueWithDisplayValue])
            ->assertCanNotSeeTableRecords([$attributeValueWithoutDisplayValue]);
    }

    public function test_can_filter_by_has_color(): void
    {
        $attribute = Attribute::factory()->create();

        $attributeValueWithColor = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'color_code' => '#FF0000',
        ]);
        $attributeValueWithoutColor = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'color_code' => null,
        ]);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->filterTable('has_color')
            ->assertCanSeeTableRecords([$attributeValueWithColor])
            ->assertCanNotSeeTableRecords([$attributeValueWithoutColor]);
    }

    public function test_can_filter_by_with_products(): void
    {
        $attribute = Attribute::factory()->create();
        $product = Product::factory()->create();

        $attributeValueWithProducts = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
        ]);
        $attributeValueWithoutProducts = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
        ]);

        // Attach product to first attribute value
        $attributeValueWithProducts->products()->attach($product);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->filterTable('with_products')
            ->assertCanSeeTableRecords([$attributeValueWithProducts])
            ->assertCanNotSeeTableRecords([$attributeValueWithoutProducts]);
    }

    public function test_can_filter_by_with_variants(): void
    {
        $attribute = Attribute::factory()->create();
        $variant = ProductVariant::factory()->create();

        $attributeValueWithVariants = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
        ]);
        $attributeValueWithoutVariants = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
        ]);

        // Attach variant to first attribute value
        $attributeValueWithVariants->variants()->attach($variant);

        Livewire::test(\App\Filament\Resources\AttributeValueResource\Pages\ListAttributeValues::class)
            ->filterTable('with_variants')
            ->assertCanSeeTableRecords([$attributeValueWithVariants])
            ->assertCanNotSeeTableRecords([$attributeValueWithoutVariants]);
    }
}
