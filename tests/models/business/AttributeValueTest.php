<?php declare(strict_types=1);

namespace Tests\Models\Business;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class AttributeValueTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_value_can_be_created(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Red',
            'slug' => 'red',
            'sort_order' => 1,
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'value' => 'Red',
            'slug' => 'red',
            'sort_order' => 1,
            'is_enabled' => true,
        ]);
    }

    public function test_attribute_value_belongs_to_attribute(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $this->assertInstanceOf(Attribute::class, $attributeValue->attribute);
        $this->assertEquals($attribute->id, $attributeValue->attribute->id);
    }

    public function test_fillable_columns_match_schema(): void
    {
        $fillable = (new AttributeValue)->getFillable();

        $expected = [
            'attribute_id',
            'value',
            'slug',
            'color_code',
            'sort_order',
            'is_enabled',
            'description',
            'hex_color',
            'image',
            'metadata',
            'display_value',
            'is_active',
            'is_default',
        ];

        foreach ($expected as $column) {
            $this->assertContains($column, $fillable);
        }
    }

    public function test_casts_handle_metadata_and_flags(): void
    {
        $attributeValue = AttributeValue::factory()->create([
            'is_enabled' => true,
            'sort_order' => 5,
            'created_at' => now(),
        ]);

        $this->assertIsBool($attributeValue->is_enabled);
        $this->assertIsInt($attributeValue->sort_order);
        $this->assertInstanceOf(\Carbon\Carbon::class, $attributeValue->created_at);
    }

    public function test_attribute_value_fillable_attributes(): void
    {
        $attributeValue = new AttributeValue();
        $fillable = $attributeValue->getFillable();

        $this->assertContains('attribute_id', $fillable);
        $this->assertContains('value', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('color_code', $fillable);
        $this->assertContains('sort_order', $fillable);
        $this->assertContains('is_enabled', $fillable);
    }

    public function test_attribute_value_scope_enabled(): void
    {
        $enabledValue = AttributeValue::factory()->create(['is_enabled' => true]);
        $disabledValue = AttributeValue::factory()->create(['is_enabled' => false]);

        $enabledValues = AttributeValue::enabled()->get();

        $this->assertTrue($enabledValues->contains($enabledValue));
        $this->assertFalse($enabledValues->contains($disabledValue));
    }

    public function test_attribute_value_scope_ordered(): void
    {
        $value1 = AttributeValue::factory()->create(['sort_order' => 2]);
        $value2 = AttributeValue::factory()->create(['sort_order' => 1]);
        $value3 = AttributeValue::factory()->create(['sort_order' => 3]);

        $orderedValues = AttributeValue::ordered()->get();

        $this->assertEquals($value2->id, $orderedValues->first()->id);
        $this->assertEquals($value3->id, $orderedValues->last()->id);
    }

    public function test_attribute_value_can_have_description(): void
    {
        $attributeValue = AttributeValue::factory()->create([
            'description' => 'Red color option',
        ]);

        $this->assertEquals('Red color option', $attributeValue->description);
    }

    public function test_attribute_value_can_have_hex_color(): void
    {
        $attributeValue = AttributeValue::factory()->create([
            'hex_color' => '#FF0000',
        ]);

        $this->assertEquals('#FF0000', $attributeValue->hex_color);
    }

    public function test_attribute_value_can_have_image(): void
    {
        $attributeValue = AttributeValue::factory()->create([
            'image' => 'red-color.jpg',
        ]);

        $this->assertEquals('red-color.jpg', $attributeValue->image);
    }

    public function test_attribute_value_can_have_metadata(): void
    {
        $attributeValue = AttributeValue::factory()->create([
            'metadata' => [
                'created_by' => 'admin',
                'version' => '1.0',
                'tags' => ['red', 'color', 'option'],
            ],
        ]);

        $this->assertIsArray($attributeValue->metadata);
        $this->assertTrue($attributeValue->is_enabled);
        $this->assertTrue($attributeValue->is_active);
        $this->assertTrue($attributeValue->is_default);
        $this->assertSame(7, $attributeValue->sort_order);
    }

    public function test_it_belongs_to_attribute(): void
    {
        $attribute1 = Attribute::factory()->create();
        $attribute2 = Attribute::factory()->create();
        
        $value1 = AttributeValue::factory()->create(['attribute_id' => $attribute1->id]);
        $value2 = AttributeValue::factory()->create(['attribute_id' => $attribute2->id]);

        $attribute1Values = AttributeValue::byAttribute($attribute1->id)->get();

        $this->assertTrue($attribute1Values->contains($value1));
        $this->assertFalse($attribute1Values->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_value(): void
    {
        $value1 = AttributeValue::factory()->create(['value' => 'Red']);
        $value2 = AttributeValue::factory()->create(['value' => 'Blue']);

        $redValues = AttributeValue::byValue('Red')->get();

        $this->assertTrue($redValues->contains($value1));
        $this->assertFalse($redValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_display_value(): void
    {
        $value1 = AttributeValue::factory()->create(['display_value' => 'Red Color']);
        $value2 = AttributeValue::factory()->create(['display_value' => 'Blue Color']);

        $redDisplayValues = AttributeValue::byDisplayValue('Red Color')->get();

        $this->assertTrue($redDisplayValues->contains($value1));
        $this->assertFalse($redDisplayValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_hex_color(): void
    {
        $value1 = AttributeValue::factory()->create(['hex_color' => '#FF0000']);
        $value2 = AttributeValue::factory()->create(['hex_color' => '#0000FF']);

        $redHexValues = AttributeValue::byHexColor('#FF0000')->get();

        $this->assertTrue($redHexValues->contains($value1));
        $this->assertFalse($redHexValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_image(): void
    {
        $value1 = AttributeValue::factory()->create(['image' => 'red-color.jpg']);
        $value2 = AttributeValue::factory()->create(['image' => 'blue-color.jpg']);

        $redImageValues = AttributeValue::byImage('red-color.jpg')->get();

        $this->assertTrue($redImageValues->contains($value1));
        $this->assertFalse($redImageValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_attribute_and_value(): void
    {
        $attribute1 = Attribute::factory()->create();
        $attribute2 = Attribute::factory()->create();
        
        $value1 = AttributeValue::factory()->create(['attribute_id' => $attribute1->id, 'value' => 'Red']);
        $value2 = AttributeValue::factory()->create(['attribute_id' => $attribute2->id, 'value' => 'Red']);

        $attribute1RedValues = AttributeValue::byAttribute($attribute1->id)->byValue('Red')->get();

        $this->assertTrue($attribute1RedValues->contains($value1));
        $this->assertFalse($attribute1RedValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_attribute_and_display_value(): void
    {
        $attribute1 = Attribute::factory()->create();
        $attribute2 = Attribute::factory()->create();
        
        $value1 = AttributeValue::factory()->create(['attribute_id' => $attribute1->id, 'display_value' => 'Red Color']);
        $value2 = AttributeValue::factory()->create(['attribute_id' => $attribute2->id, 'display_value' => 'Red Color']);

        $attribute1RedDisplayValues = AttributeValue::byAttribute($attribute1->id)->byDisplayValue('Red Color')->get();

        $this->assertTrue($attribute1RedDisplayValues->contains($value1));
        $this->assertFalse($attribute1RedDisplayValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_attribute_and_hex_color(): void
    {
        $attribute1 = Attribute::factory()->create();
        $attribute2 = Attribute::factory()->create();
        
        $value1 = AttributeValue::factory()->create(['attribute_id' => $attribute1->id, 'hex_color' => '#FF0000']);
        $value2 = AttributeValue::factory()->create(['attribute_id' => $attribute2->id, 'hex_color' => '#FF0000']);

        $attribute1RedHexValues = AttributeValue::byAttribute($attribute1->id)->byHexColor('#FF0000')->get();

        $this->assertTrue($attribute1RedHexValues->contains($value1));
        $this->assertFalse($attribute1RedHexValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_attribute_and_image(): void
    {
        $attribute1 = Attribute::factory()->create();
        $attribute2 = Attribute::factory()->create();
        
        $value1 = AttributeValue::factory()->create(['attribute_id' => $attribute1->id, 'image' => 'red-color.jpg']);
        $value2 = AttributeValue::factory()->create(['attribute_id' => $attribute2->id, 'image' => 'red-color.jpg']);

        $attribute1RedImageValues = AttributeValue::byAttribute($attribute1->id)->byImage('red-color.jpg')->get();

        $this->assertTrue($attribute1RedImageValues->contains($value1));
        $this->assertFalse($attribute1RedImageValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_value_and_display_value(): void
    {
        $value1 = AttributeValue::factory()->create(['value' => 'Red', 'display_value' => 'Red Color']);
        $value2 = AttributeValue::factory()->create(['value' => 'Blue', 'display_value' => 'Red Color']);

        $redValueRedDisplayValues = AttributeValue::byValue('Red')->byDisplayValue('Red Color')->get();

        $this->assertTrue($redValueRedDisplayValues->contains($value1));
        $this->assertFalse($redValueRedDisplayValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_value_and_hex_color(): void
    {
        $value1 = AttributeValue::factory()->create(['value' => 'Red', 'hex_color' => '#FF0000']);
        $value2 = AttributeValue::factory()->create(['value' => 'Blue', 'hex_color' => '#FF0000']);

        $redValueRedHexValues = AttributeValue::byValue('Red')->byHexColor('#FF0000')->get();

        $this->assertTrue($redValueRedHexValues->contains($value1));
        $this->assertFalse($redValueRedHexValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_value_and_image(): void
    {
        $value1 = AttributeValue::factory()->create(['value' => 'Red', 'image' => 'red-color.jpg']);
        $value2 = AttributeValue::factory()->create(['value' => 'Blue', 'image' => 'red-color.jpg']);

        $redValueRedImageValues = AttributeValue::byValue('Red')->byImage('red-color.jpg')->get();

        $this->assertTrue($redValueRedImageValues->contains($value1));
        $this->assertFalse($redValueRedImageValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_display_value_and_hex_color(): void
    {
        $value1 = AttributeValue::factory()->create(['display_value' => 'Red Color', 'hex_color' => '#FF0000']);
        $value2 = AttributeValue::factory()->create(['display_value' => 'Blue Color', 'hex_color' => '#FF0000']);

        $redDisplayRedHexValues = AttributeValue::byDisplayValue('Red Color')->byHexColor('#FF0000')->get();

        $this->assertTrue($redDisplayRedHexValues->contains($value1));
        $this->assertFalse($redDisplayRedHexValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_display_value_and_image(): void
    {
        $value1 = AttributeValue::factory()->create(['display_value' => 'Red Color', 'image' => 'red-color.jpg']);
        $value2 = AttributeValue::factory()->create(['display_value' => 'Blue Color', 'image' => 'red-color.jpg']);

        $redDisplayRedImageValues = AttributeValue::byDisplayValue('Red Color')->byImage('red-color.jpg')->get();

        $this->assertTrue($redDisplayRedImageValues->contains($value1));
        $this->assertFalse($redDisplayRedImageValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_hex_color_and_image(): void
    {
        $value1 = AttributeValue::factory()->create(['hex_color' => '#FF0000', 'image' => 'red-color.jpg']);
        $value2 = AttributeValue::factory()->create(['hex_color' => '#0000FF', 'image' => 'red-color.jpg']);

        $redHexRedImageValues = AttributeValue::byHexColor('#FF0000')->byImage('red-color.jpg')->get();

        $this->assertTrue($redHexRedImageValues->contains($value1));
        $this->assertFalse($redHexRedImageValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_all_attributes(): void
    {
        $attribute1 = Attribute::factory()->create();
        $attribute2 = Attribute::factory()->create();
        
        $value1 = AttributeValue::factory()->create([
            'attribute_id' => $attribute1->id,
            'value' => 'Red',
            'display_value' => 'Red Color',
            'hex_color' => '#FF0000',
            'image' => 'red-color.jpg'
        ]);
        $value2 = AttributeValue::factory()->create([
            'attribute_id' => $attribute2->id,
            'value' => 'Red',
            'display_value' => 'Red Color',
            'hex_color' => '#FF0000',
            'image' => 'red-color.jpg'
        ]);

        $this->assertTrue($attributeValue->attribute->is($this->attribute));
    }

    public function test_products_relationship_uses_product_attributes_pivot(): void
    {
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
        ]);

        $activeOrderedValues = AttributeValue::active()->ordered()->get();

        $attributeValue->products()->attach($product->id, [
            'attribute_id' => $this->attribute->id,
        ]);

        $related = $attributeValue->products()->first();

        $this->assertNotNull($related);
        $this->assertTrue($related->is($product));
        $this->assertSame($this->attribute->id, $related->pivot->attribute_id);
    }

    public function test_variants_relationship_uses_product_variant_attributes_pivot(): void
    {
        $attribute1 = Attribute::factory()->create();
        $attribute2 = Attribute::factory()->create();
        
        $value1 = AttributeValue::factory()->create(['attribute_id' => $attribute1->id, 'is_active' => true]);
        $value2 = AttributeValue::factory()->create(['attribute_id' => $attribute2->id, 'is_active' => true]);

        $attribute1ActiveValues = AttributeValue::byAttribute($attribute1->id)->active()->get();

        $attributeValue->variants()->attach($variant->id, [
            'attribute_id' => $this->attribute->id,
        ]);

        $related = $attributeValue->variants()->first();

        $this->assertNotNull($related);
        $this->assertTrue($related->is($variant));
        $this->assertSame($this->attribute->id, $related->pivot->attribute_id);
    }

    public function test_enabled_scope_filters_records_when_global_scope_removed(): void
    {
        $attribute1 = Attribute::factory()->create();
        
        $value1 = AttributeValue::factory()->create(['attribute_id' => $attribute1->id, 'sort_order' => 1]);
        $value2 = AttributeValue::factory()->create(['attribute_id' => $attribute1->id, 'sort_order' => 2]);

        $results = AttributeValue::query()
            ->withoutGlobalScope(EnabledScope::class)
            ->enabled()
            ->pluck('id')
            ->all();

        $this->assertContains($enabled->id, $results);
        $this->assertNotContains($disabled->id, $results);
    }

    public function test_active_scope_filters_records_when_global_scope_removed(): void
    {
        $active = AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
        ]);

        $redActiveValues = AttributeValue::byValue('Red')->active()->get();

        $results = AttributeValue::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->active()
            ->pluck('id')
            ->all();

        $this->assertContains($active->id, $results);
        $this->assertNotContains($inactive->id, $results);
    }

    public function test_ordered_scope_sorts_by_sort_order(): void
    {
        $value1 = AttributeValue::factory()->create(['value' => 'Red', 'sort_order' => 1]);
        $value2 = AttributeValue::factory()->create(['value' => 'Red', 'sort_order' => 2]);

        $second = AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
            'sort_order' => 2,
        ]);

        $third = AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
            'sort_order' => 3,
        ]);

        $orderedIds = AttributeValue::query()
            ->ordered()
            ->pluck('id')
            ->all();

        $this->assertEquals([$first->id, $second->id, $third->id], $orderedIds);
    }

    public function test_filter_scopes_match_column_values(): void
    {
        $matching = AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
            'value' => 'Red',
            'display_value' => 'Bright Red',
            'hex_color' => '#FF0000',
            'image' => 'red.png',
        ]);

        $redDisplayActiveValues = AttributeValue::byDisplayValue('Red Color')->active()->get();

        $otherAttribute = Attribute::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
            'is_visible' => true,
        ]);

    public function test_attribute_value_can_have_scope_by_display_value_and_ordered(): void
    {
        $value1 = AttributeValue::factory()->create(['display_value' => 'Red Color', 'sort_order' => 1]);
        $value2 = AttributeValue::factory()->create(['display_value' => 'Red Color', 'sort_order' => 2]);

        $this->assertEquals(
            [$matching->id],
            AttributeValue::forAttribute($this->attribute->id)->pluck('id')->all()
        );

        $this->assertEquals(
            [$matching->id],
            AttributeValue::byValue('Red')->pluck('id')->all()
        );

    public function test_attribute_value_can_have_scope_by_hex_color_and_active(): void
    {
        $value1 = AttributeValue::factory()->create(['hex_color' => '#FF0000', 'is_active' => true]);
        $value2 = AttributeValue::factory()->create(['hex_color' => '#0000FF', 'is_active' => true]);

        $redHexActiveValues = AttributeValue::byHexColor('#FF0000')->active()->get();

        $this->assertTrue($redHexActiveValues->contains($value1));
        $this->assertFalse($redHexActiveValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_hex_color_and_ordered(): void
    {
        $value1 = AttributeValue::factory()->create(['hex_color' => '#FF0000', 'sort_order' => 1]);
        $value2 = AttributeValue::factory()->create(['hex_color' => '#0000FF', 'sort_order' => 2]);

        $redHexOrderedValues = AttributeValue::byHexColor('#FF0000')->ordered()->get();

        $this->assertEquals($value1->id, $redHexOrderedValues->first()->id);
        $this->assertEquals($value2->id, $redHexOrderedValues->last()->id);
    }

    public function test_attribute_value_can_have_scope_by_image_and_active(): void
    {
        $value1 = AttributeValue::factory()->create(['image' => 'red-color.jpg', 'is_active' => true]);
        $value2 = AttributeValue::factory()->create(['image' => 'blue-color.jpg', 'is_active' => true]);

        $redImageActiveValues = AttributeValue::byImage('red-color.jpg')->active()->get();

        $this->assertTrue($redImageActiveValues->contains($value1));
        $this->assertFalse($redImageActiveValues->contains($value2));
    }

    public function test_attribute_value_can_have_scope_by_image_and_ordered(): void
    {
        $value1 = AttributeValue::factory()->create(['image' => 'red-color.jpg', 'sort_order' => 1]);
        $value2 = AttributeValue::factory()->create(['image' => 'blue-color.jpg', 'sort_order' => 2]);

        $redImageOrderedValues = AttributeValue::byImage('red-color.jpg')->ordered()->get();

        $this->assertEquals($value1->id, $redImageOrderedValues->first()->id);
        $this->assertEquals($value2->id, $redImageOrderedValues->last()->id);
    }
}