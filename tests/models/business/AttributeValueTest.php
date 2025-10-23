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

    private Attribute $attribute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attribute = Attribute::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
            'is_visible' => true,
        ]);
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
            'attribute_id' => $this->attribute->id,
            'metadata' => ['created_by' => 'tester'],
            'is_default' => true,
            'sort_order' => 7,
        ]);

        $this->assertIsArray($attributeValue->metadata);
        $this->assertTrue($attributeValue->is_enabled);
        $this->assertTrue($attributeValue->is_active);
        $this->assertTrue($attributeValue->is_default);
        $this->assertSame(7, $attributeValue->sort_order);
    }

    public function test_it_belongs_to_attribute(): void
    {
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
        ]);

        $this->assertTrue($attributeValue->attribute->is($this->attribute));
    }

    public function test_products_relationship_uses_product_attributes_pivot(): void
    {
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'published_at' => Carbon::now()->subDay(),
            'is_visible' => true,
        ]);

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
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
        ]);

        $product = Product::factory()->create([
            'status' => 'published',
            'published_at' => Carbon::now()->subDay(),
            'is_visible' => true,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'status' => 'active',
        ]);

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
        $enabled = AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
            'is_enabled' => true,
        ]);

        $disabled = AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
            'is_enabled' => false,
        ]);

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

        $inactive = AttributeValue::factory()->inactive()->create([
            'attribute_id' => $this->attribute->id,
        ]);

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
        $first = AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
            'sort_order' => 1,
        ]);

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

        AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
            'value' => 'Blue',
            'display_value' => 'Ocean Blue',
            'hex_color' => '#0000FF',
            'image' => 'blue.png',
        ]);

        $otherAttribute = Attribute::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
            'is_visible' => true,
        ]);

        AttributeValue::factory()->create([
            'attribute_id' => $otherAttribute->id,
            'value' => 'Red',
            'display_value' => 'Bright Red',
            'hex_color' => '#FF0000',
            'image' => 'red.png',
        ]);

        $this->assertEquals(
            [$matching->id],
            AttributeValue::forAttribute($this->attribute->id)->pluck('id')->all()
        );

        $this->assertEquals(
            [$matching->id],
            AttributeValue::byValue('Red')->pluck('id')->all()
        );

        $this->assertEquals(
            [$matching->id],
            AttributeValue::byDisplayValue('Bright Red')->pluck('id')->all()
        );

        $this->assertEquals(
            [$matching->id],
            AttributeValue::byHexColor('#FF0000')->pluck('id')->all()
        );

        $this->assertEquals(
            [$matching->id],
            AttributeValue::byImage('red.png')->pluck('id')->all()
        );
    }
}