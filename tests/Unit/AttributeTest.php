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
}
