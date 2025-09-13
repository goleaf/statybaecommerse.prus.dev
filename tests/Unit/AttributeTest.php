<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_can_be_created(): void
    {
        $attribute = Attribute::factory()->create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'is_required' => true,
            'is_filterable' => true,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('attributes', [
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'is_required' => true,
            'is_filterable' => true,
            'is_visible' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_attribute_has_many_values(): void
    {
        $attribute = Attribute::factory()->create();
        $value1 = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);
        $value2 = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $attribute->values);
        $this->assertCount(2, $attribute->values);
        $this->assertTrue($attribute->values->contains($value1));
        $this->assertTrue($attribute->values->contains($value2));
    }

    public function test_attribute_belongs_to_many_products(): void
    {
        $attribute = Attribute::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $attribute->products()->attach([$product1->id, $product2->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $attribute->products);
        $this->assertCount(2, $attribute->products);
        $this->assertTrue($attribute->products->contains($product1));
        $this->assertTrue($attribute->products->contains($product2));
    }

    public function test_attribute_casts_work_correctly(): void
    {
        $attribute = Attribute::factory()->create([
            'is_required' => true,
            'is_filterable' => false,
            'is_visible' => true,
            'sort_order' => 5,
            'created_at' => now(),
        ]);

        $this->assertIsBool($attribute->is_required);
        $this->assertIsBool($attribute->is_filterable);
        $this->assertIsBool($attribute->is_visible);
        $this->assertIsInt($attribute->sort_order);
        $this->assertInstanceOf(\Carbon\Carbon::class, $attribute->created_at);
    }

    public function test_attribute_fillable_attributes(): void
    {
        $attribute = new Attribute();
        $fillable = $attribute->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('is_required', $fillable);
        $this->assertContains('is_filterable', $fillable);
    }

    public function test_attribute_scope_required(): void
    {
        $requiredAttribute = Attribute::factory()->create(['is_required' => true]);
        $optionalAttribute = Attribute::factory()->create(['is_required' => false]);

        $requiredAttributes = Attribute::required()->get();

        $this->assertTrue($requiredAttributes->contains($requiredAttribute));
        $this->assertFalse($requiredAttributes->contains($optionalAttribute));
    }

    public function test_attribute_scope_filterable(): void
    {
        $filterableAttribute = Attribute::factory()->create(['is_filterable' => true]);
        $nonFilterableAttribute = Attribute::factory()->create(['is_filterable' => false]);

        $filterableAttributes = Attribute::filterable()->get();

        $this->assertTrue($filterableAttributes->contains($filterableAttribute));
        $this->assertFalse($filterableAttributes->contains($nonFilterableAttribute));
    }

    public function test_attribute_scope_visible(): void
    {
        $visibleAttribute = Attribute::factory()->create(['is_visible' => true]);
        $hiddenAttribute = Attribute::factory()->create(['is_visible' => false]);

        $visibleAttributes = Attribute::visible()->get();

        $this->assertTrue($visibleAttributes->contains($visibleAttribute));
        $this->assertFalse($visibleAttributes->contains($hiddenAttribute));
    }

    public function test_attribute_scope_ordered(): void
    {
        $attribute1 = Attribute::factory()->create(['sort_order' => 2]);
        $attribute2 = Attribute::factory()->create(['sort_order' => 1]);
        $attribute3 = Attribute::factory()->create(['sort_order' => 3]);

        $orderedAttributes = Attribute::ordered()->get();

        $this->assertEquals($attribute2->id, $orderedAttributes->first()->id);
        $this->assertEquals($attribute3->id, $orderedAttributes->last()->id);
    }

    public function test_attribute_can_have_description(): void
    {
        $attribute = Attribute::factory()->create([
            'description' => 'Product color attribute',
        ]);

        $this->assertEquals('Product color attribute', $attribute->description);
    }

    public function test_attribute_can_have_validation_rules(): void
    {
        $attribute = Attribute::factory()->create([
            'validation_rules' => ['required' => true, 'max' => 255],
        ]);

        $this->assertIsArray($attribute->validation_rules);
        $this->assertTrue($attribute->validation_rules['required']);
        $this->assertEquals(255, $attribute->validation_rules['max']);
    }

    public function test_attribute_can_have_default_value(): void
    {
        $attribute = Attribute::factory()->create([
            'default_value' => 'red',
        ]);

        $this->assertEquals('red', $attribute->default_value);
    }

    public function test_attribute_can_have_meta_data(): void
    {
        $attribute = Attribute::factory()->create([
            'meta_data' => [
                'created_by' => 'admin',
                'version' => '1.0',
                'tags' => ['color', 'product', 'attribute'],
            ],
        ]);

        $this->assertIsArray($attribute->meta_data);
        $this->assertEquals('admin', $attribute->meta_data['created_by']);
        $this->assertEquals('1.0', $attribute->meta_data['version']);
        $this->assertIsArray($attribute->meta_data['tags']);
    }

    public function test_attribute_can_have_scope_by_type(): void
    {
        $attribute1 = Attribute::factory()->create(['type' => 'select']);
        $attribute2 = Attribute::factory()->create(['type' => 'text']);

        $selectAttributes = Attribute::byType('select')->get();

        $this->assertTrue($selectAttributes->contains($attribute1));
        $this->assertFalse($selectAttributes->contains($attribute2));
    }

    public function test_attribute_can_have_scope_by_category(): void
    {
        $categoryId = 1;
        $attribute1 = Attribute::factory()->create(['category_id' => $categoryId]);
        $attribute2 = Attribute::factory()->create(['category_id' => 2]);

        $categoryAttributes = Attribute::byCategory($categoryId)->get();

        $this->assertTrue($categoryAttributes->contains($attribute1));
        $this->assertFalse($categoryAttributes->contains($attribute2));
    }

    public function test_attribute_can_have_scope_by_group(): void
    {
        $groupName = 'basic_info';
        $attribute1 = Attribute::factory()->create(['group_name' => $groupName]);
        $attribute2 = Attribute::factory()->create(['group_name' => 'technical_specs']);

        $groupAttributes = Attribute::byGroup($groupName)->get();

        $this->assertTrue($groupAttributes->contains($attribute1));
        $this->assertFalse($groupAttributes->contains($attribute2));
    }

    public function test_attribute_can_have_scope_enabled(): void
    {
        $enabledAttribute = Attribute::factory()->create(['is_enabled' => true]);
        $disabledAttribute = Attribute::factory()->create(['is_enabled' => false]);

        $enabledAttributes = Attribute::enabled()->get();

        $this->assertTrue($enabledAttributes->contains($enabledAttribute));
        $this->assertFalse($enabledAttributes->contains($disabledAttribute));
    }

    public function test_attribute_can_have_scope_searchable(): void
    {
        $searchableAttribute = Attribute::factory()->create(['is_searchable' => true]);
        $nonSearchableAttribute = Attribute::factory()->create(['is_searchable' => false]);

        $searchableAttributes = Attribute::searchable()->get();

        $this->assertTrue($searchableAttributes->contains($searchableAttribute));
        $this->assertFalse($searchableAttributes->contains($nonSearchableAttribute));
    }

    public function test_attribute_can_have_scope_editable(): void
    {
        $editableAttribute = Attribute::factory()->create(['is_editable' => true]);
        $nonEditableAttribute = Attribute::factory()->create(['is_editable' => false]);

        $editableAttributes = Attribute::editable()->get();

        $this->assertTrue($editableAttributes->contains($editableAttribute));
        $this->assertFalse($editableAttributes->contains($nonEditableAttribute));
    }

    public function test_attribute_can_have_scope_sortable(): void
    {
        $sortableAttribute = Attribute::factory()->create(['is_sortable' => true]);
        $nonSortableAttribute = Attribute::factory()->create(['is_sortable' => false]);

        $sortableAttributes = Attribute::sortable()->get();

        $this->assertTrue($sortableAttributes->contains($sortableAttribute));
        $this->assertFalse($sortableAttributes->contains($nonSortableAttribute));
    }

    public function test_attribute_can_have_scope_with_values(): void
    {
        $attributeWithValues = Attribute::factory()->create();
        AttributeValue::factory()->create(['attribute_id' => $attributeWithValues->id]);

        $attributeWithoutValues = Attribute::factory()->create();

        $attributesWithValues = Attribute::withValues()->get();

        $this->assertTrue($attributesWithValues->contains($attributeWithValues));
        $this->assertFalse($attributesWithValues->contains($attributeWithoutValues));
    }

    public function test_attribute_can_have_scope_with_enabled_values(): void
    {
        $attribute = Attribute::factory()->create();
        AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'is_enabled' => true]);
        AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'is_enabled' => false]);

        $attributesWithEnabledValues = Attribute::withEnabledValues()->get();

        $this->assertTrue($attributesWithEnabledValues->contains($attribute));
    }

    public function test_attribute_type_helper_methods(): void
    {
        $textAttribute = Attribute::factory()->create(['type' => 'text']);
        $selectAttribute = Attribute::factory()->create(['type' => 'select']);
        $numberAttribute = Attribute::factory()->create(['type' => 'number']);
        $booleanAttribute = Attribute::factory()->create(['type' => 'boolean']);
        $dateAttribute = Attribute::factory()->create(['type' => 'date']);
        $fileAttribute = Attribute::factory()->create(['type' => 'file']);

        $this->assertTrue($textAttribute->isTextType());
        $this->assertFalse($textAttribute->isSelectType());
        $this->assertFalse($textAttribute->isNumericType());
        $this->assertFalse($textAttribute->isBooleanType());
        $this->assertFalse($textAttribute->isDateType());
        $this->assertFalse($textAttribute->isFileType());

        $this->assertTrue($selectAttribute->isSelectType());
        $this->assertTrue($numberAttribute->isNumericType());
        $this->assertTrue($booleanAttribute->isBooleanType());
        $this->assertTrue($dateAttribute->isDateType());
        $this->assertTrue($fileAttribute->isFileType());
    }

    public function test_attribute_can_have_multiple_values(): void
    {
        $multiselectAttribute = Attribute::factory()->create(['type' => 'multiselect']);
        $fileAttribute = Attribute::factory()->create(['type' => 'file']);
        $textAttribute = Attribute::factory()->create(['type' => 'text']);

        $this->assertTrue($multiselectAttribute->canHaveMultipleValues());
        $this->assertTrue($fileAttribute->canHaveMultipleValues());
        $this->assertFalse($textAttribute->canHaveMultipleValues());
    }

    public function test_attribute_get_default_value_for_type(): void
    {
        $textAttribute = Attribute::factory()->create(['type' => 'text']);
        $numberAttribute = Attribute::factory()->create(['type' => 'number']);
        $booleanAttribute = Attribute::factory()->create(['type' => 'boolean']);
        $colorAttribute = Attribute::factory()->create(['type' => 'color']);

        $this->assertEquals('', $textAttribute->getDefaultValueForType());
        $this->assertEquals(0, $numberAttribute->getDefaultValueForType());
        $this->assertEquals(false, $booleanAttribute->getDefaultValueForType());
        $this->assertEquals('#000000', $colorAttribute->getDefaultValueForType());
    }

    public function test_attribute_get_values_count(): void
    {
        $attribute = Attribute::factory()->create();
        AttributeValue::factory()->count(3)->create(['attribute_id' => $attribute->id]);

        $this->assertEquals(3, $attribute->getValuesCount());
    }

    public function test_attribute_get_enabled_values_count(): void
    {
        $attribute = Attribute::factory()->create();
        AttributeValue::factory()->count(2)->create(['attribute_id' => $attribute->id, 'is_enabled' => true]);
        AttributeValue::factory()->count(1)->create(['attribute_id' => $attribute->id, 'is_enabled' => false]);

        $this->assertEquals(2, $attribute->getEnabledValuesCount());
    }

    public function test_attribute_display_name_accessor(): void
    {
        $attribute = Attribute::factory()->create(['name' => 'Test Attribute']);
        $this->assertEquals('Test Attribute', $attribute->display_name);

        $attributeWithoutName = Attribute::factory()->create(['name' => null, 'slug' => 'test-slug']);
        $this->assertEquals('test-slug', $attributeWithoutName->display_name);
    }

    public function test_attribute_type_icon_accessor(): void
    {
        $textAttribute = Attribute::factory()->create(['type' => 'text']);
        $this->assertEquals('heroicon-o-document-text', $textAttribute->type_icon);

        $selectAttribute = Attribute::factory()->create(['type' => 'select']);
        $this->assertEquals('heroicon-o-list-bullet', $selectAttribute->type_icon);

        $unknownAttribute = Attribute::factory()->create(['type' => 'unknown']);
        $this->assertEquals('heroicon-o-adjustments-horizontal', $unknownAttribute->type_icon);
    }

    public function test_attribute_type_color_accessor(): void
    {
        $textAttribute = Attribute::factory()->create(['type' => 'text']);
        $this->assertEquals('gray', $textAttribute->type_color);

        $numberAttribute = Attribute::factory()->create(['type' => 'number']);
        $this->assertEquals('blue', $numberAttribute->type_color);

        $unknownAttribute = Attribute::factory()->create(['type' => 'unknown']);
        $this->assertEquals('gray', $unknownAttribute->type_color);
    }

    public function test_attribute_validation_rules_for_form(): void
    {
        $requiredAttribute = Attribute::factory()->create([
            'is_required' => true,
            'validation_rules' => ['max' => 255]
        ]);

        $rules = $requiredAttribute->getValidationRulesForForm();
        $this->assertContains('required', $rules);
        $this->assertContains('max', $rules);

        $optionalAttribute = Attribute::factory()->create([
            'is_required' => false,
            'validation_rules' => ['min' => 1]
        ]);

        $rules = $optionalAttribute->getValidationRulesForForm();
        $this->assertNotContains('required', $rules);
        $this->assertContains('min', $rules);
    }

    public function test_attribute_form_component_config(): void
    {
        $attribute = Attribute::factory()->create([
            'name' => 'Test Attribute',
            'type' => 'select',
            'placeholder' => 'Select option',
            'help_text' => 'Choose an option',
            'is_required' => true,
            'default_value' => 'option1',
            'min_value' => 1,
            'max_value' => 10,
            'step_value' => 0.5,
        ]);

        $config = $attribute->getFormComponentConfig();

        $this->assertEquals('select', $config['type']);
        $this->assertEquals('Test Attribute', $config['label']);
        $this->assertEquals('Select option', $config['placeholder']);
        $this->assertEquals('Choose an option', $config['help_text']);
        $this->assertTrue($config['required']);
        $this->assertEquals('option1', $config['default_value']);
        $this->assertEquals(1, $config['min_value']);
        $this->assertEquals(10, $config['max_value']);
        $this->assertEquals(0.5, $config['step_value']);
    }

    public function test_attribute_usage_count(): void
    {
        $attribute = Attribute::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $attribute->products()->attach([$product1->id, $product2->id]);

        $this->assertEquals(2, $attribute->getUsageCount());
        $this->assertTrue($attribute->isUsedInProducts());
    }

    public function test_attribute_popularity_score(): void
    {
        $attribute = Attribute::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $attribute->products()->attach([$product1->id, $product2->id]);
        AttributeValue::factory()->count(3)->create(['attribute_id' => $attribute->id, 'is_enabled' => true]);
        AttributeValue::factory()->count(1)->create(['attribute_id' => $attribute->id, 'is_enabled' => false]);

        $score = $attribute->getPopularityScore();
        // Score calculation: (2 products * 10) + (4 values * 2) + (3 enabled values * 1) = 20 + 8 + 3 = 31
        $this->assertEquals(31, $score);
    }

    public function test_attribute_status_badge(): void
    {
        $disabledAttribute = Attribute::factory()->create(['is_enabled' => false]);
        $this->assertEquals('disabled', $disabledAttribute->status_badge);
        $this->assertEquals('gray', $disabledAttribute->status_color);

        $requiredAttribute = Attribute::factory()->create(['is_enabled' => true, 'is_required' => true]);
        $this->assertEquals('required', $requiredAttribute->status_badge);
        $this->assertEquals('red', $requiredAttribute->status_color);

        $filterableAttribute = Attribute::factory()->create(['is_enabled' => true, 'is_required' => false, 'is_filterable' => true]);
        $this->assertEquals('filterable', $filterableAttribute->status_badge);
        $this->assertEquals('blue', $filterableAttribute->status_color);

        $standardAttribute = Attribute::factory()->create(['is_enabled' => true, 'is_required' => false, 'is_filterable' => false]);
        $this->assertEquals('standard', $standardAttribute->status_badge);
        $this->assertEquals('green', $standardAttribute->status_color);
    }

    public function test_attribute_statistics(): void
    {
        $attribute = Attribute::factory()->create();
        $product = Product::factory()->create();
        $attribute->products()->attach($product->id);
        AttributeValue::factory()->count(2)->create(['attribute_id' => $attribute->id, 'is_enabled' => true]);

        $statistics = $attribute->getStatistics();

        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('usage_count', $statistics);
        $this->assertArrayHasKey('values_count', $statistics);
        $this->assertArrayHasKey('enabled_values_count', $statistics);
        $this->assertArrayHasKey('popularity_score', $statistics);
        $this->assertArrayHasKey('status', $statistics);
        $this->assertArrayHasKey('status_color', $statistics);
        $this->assertArrayHasKey('status_label', $statistics);

        $this->assertEquals(1, $statistics['usage_count']);
        $this->assertEquals(2, $statistics['values_count']);
        $this->assertEquals(2, $statistics['enabled_values_count']);
    }

    public function test_attribute_duplicate_for_group(): void
    {
        $originalAttribute = Attribute::factory()->create(['group_name' => 'original_group']);
        AttributeValue::factory()->count(2)->create(['attribute_id' => $originalAttribute->id]);

        $duplicate = $originalAttribute->duplicateForGroup('new_group');

        $this->assertNotEquals($originalAttribute->id, $duplicate->id);
        $this->assertEquals('new_group', $duplicate->group_name);
        $this->assertStringContains('(Copy)', $duplicate->name);
        $this->assertStringContains('-copy', $duplicate->slug);
        $this->assertEquals(2, $duplicate->values()->count());
    }

    public function test_attribute_merge_with(): void
    {
        $attribute1 = Attribute::factory()->create();
        $attribute2 = Attribute::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $attribute1->products()->attach($product1->id);
        $attribute2->products()->attach($product2->id);

        AttributeValue::factory()->create(['attribute_id' => $attribute1->id]);
        AttributeValue::factory()->create(['attribute_id' => $attribute2->id]);

        $merged = $attribute1->mergeWith($attribute2);

        $this->assertEquals($attribute1->id, $merged->id);
        $this->assertEquals(2, $merged->values()->count());
        $this->assertFalse(Attribute::where('id', $attribute2->id)->exists());
    }
}
