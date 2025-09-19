<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Translations\AttributeValueTranslation;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeValueControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_values_index_page_loads(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $response = $this->get(route('attribute-values.index'));

        $response->assertStatus(200);
        $response->assertViewIs('attribute-values.index');
        $response->assertViewHas('attributeValues');
        $response->assertViewHas('attributes');
    }

    public function test_attribute_values_index_with_filters(): void
    {
        $attribute1 = Attribute::factory()->create();
        $attribute2 = Attribute::factory()->create();

        $attributeValue1 = AttributeValue::factory()->create([
            'attribute_id' => $attribute1->id,
            'value' => 'Red',
            'color_code' => '#FF0000',
        ]);

        $attributeValue2 = AttributeValue::factory()->create([
            'attribute_id' => $attribute2->id,
            'value' => 'Blue',
        ]);

        // Filter by attribute
        $response = $this->get(route('attribute-values.index', ['attribute_id' => $attribute1->id]));
        $response->assertStatus(200);
        $response->assertViewHas('attributeValues', function ($attributeValues) use ($attributeValue1, $attributeValue2) {
            return $attributeValues->contains($attributeValue1) && !$attributeValues->contains($attributeValue2);
        });

        // Filter by search
        $response = $this->get(route('attribute-values.index', ['search' => 'Red']));
        $response->assertStatus(200);
        $response->assertViewHas('attributeValues', function ($attributeValues) use ($attributeValue1, $attributeValue2) {
            return $attributeValues->contains($attributeValue1) && !$attributeValues->contains($attributeValue2);
        });

        // Filter by color
        $response = $this->get(route('attribute-values.index', ['with_color' => '1']));
        $response->assertStatus(200);
        $response->assertViewHas('attributeValues', function ($attributeValues) use ($attributeValue1, $attributeValue2) {
            return $attributeValues->contains($attributeValue1) && !$attributeValues->contains($attributeValue2);
        });
    }

    public function test_attribute_value_show_page_loads(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $response = $this->get(route('attribute-values.show', $attributeValue));

        $response->assertStatus(200);
        $response->assertViewIs('attribute-values.show');
        $response->assertViewHas('attributeValue', $attributeValue);
    }

    public function test_attribute_value_show_page_with_relations(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);
        $product = Product::factory()->create();
        $attributeValue->products()->attach($product->id);

        $response = $this->get(route('attribute-values.show', $attributeValue));

        $response->assertStatus(200);
        $response->assertViewHas('attributeValue', function ($loadedAttributeValue) use ($attributeValue) {
            return $loadedAttributeValue->id === $attributeValue->id &&
                $loadedAttributeValue->relationLoaded('attribute') &&
                $loadedAttributeValue->relationLoaded('products') &&
                $loadedAttributeValue->relationLoaded('variants') &&
                $loadedAttributeValue->relationLoaded('translations');
        });
    }

    public function test_attribute_values_by_attribute_page_loads(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue1 = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);
        $attributeValue2 = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $response = $this->get(route('attribute-values.by-attribute', $attribute));

        $response->assertStatus(200);
        $response->assertViewIs('attribute-values.by-attribute');
        $response->assertViewHas('attribute', $attribute);
        $response->assertViewHas('attributeValues', function ($attributeValues) use ($attributeValue1, $attributeValue2) {
            return $attributeValues->contains($attributeValue1) && $attributeValues->contains($attributeValue2);
        });
    }

    public function test_attribute_values_api_returns_json(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $response = $this->get(route('attribute-values.api'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'value',
                    'description',
                    'color_code',
                    'attribute' => [
                        'id',
                        'name',
                    ],
                    'products_count',
                    'variants_count',
                ],
            ],
            'meta' => [
                'total',
            ],
        ]);
    }

    public function test_attribute_values_api_with_filters(): void
    {
        $attribute1 = Attribute::factory()->create();
        $attribute2 = Attribute::factory()->create();

        $attributeValue1 = AttributeValue::factory()->create([
            'attribute_id' => $attribute1->id,
            'value' => 'Red',
        ]);

        $attributeValue2 = AttributeValue::factory()->create([
            'attribute_id' => $attribute2->id,
            'value' => 'Blue',
        ]);

        // Filter by attribute
        $response = $this->get(route('attribute-values.api', ['attribute_id' => $attribute1->id]));
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($attributeValue1->id, $data[0]['id']);

        // Filter by search
        $response = $this->get(route('attribute-values.api', ['search' => 'Red']));
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($attributeValue1->id, $data[0]['id']);
    }

    public function test_attribute_values_search_api(): void
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

        $response = $this->get(route('attribute-values.search', ['q' => 'Red']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'value',
                    'attribute_name',
                ],
            ],
        ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($attributeValue1->id, $data[0]['id']);
    }

    public function test_attribute_values_search_api_with_empty_query(): void
    {
        $response = $this->get(route('attribute-values.search', ['q' => '']));

        $response->assertStatus(200);
        $response->assertJson(['data' => []]);
    }

    public function test_attribute_values_search_api_with_attribute_filter(): void
    {
        $attribute1 = Attribute::factory()->create();
        $attribute2 = Attribute::factory()->create();

        $attributeValue1 = AttributeValue::factory()->create([
            'attribute_id' => $attribute1->id,
            'value' => 'Red',
        ]);

        $attributeValue2 = AttributeValue::factory()->create([
            'attribute_id' => $attribute2->id,
            'value' => 'Red',
        ]);

        $response = $this->get(route('attribute-values.search', [
            'q' => 'Red',
            'attribute_id' => $attribute1->id,
        ]));

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($attributeValue1->id, $data[0]['id']);
    }

    public function test_attribute_value_with_translations_display_correctly(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Default Value',
        ]);

        // Create translations
        AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'en',
            'value' => 'English Value',
        ]);

        AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'lt',
            'value' => 'Lithuanian Value',
        ]);

        // Test with English locale
        app()->setLocale('en');
        $response = $this->get(route('attribute-values.show', $attributeValue));
        $response->assertStatus(200);
        $response->assertSee('English Value');

        // Test with Lithuanian locale
        app()->setLocale('lt');
        $response = $this->get(route('attribute-values.show', $attributeValue));
        $response->assertStatus(200);
        $response->assertSee('Lithuanian Value');
    }

    public function test_attribute_value_pagination_works(): void
    {
        $attribute = Attribute::factory()->create();

        // Create 25 attribute values
        AttributeValue::factory()->count(25)->create(['attribute_id' => $attribute->id]);

        $response = $this->get(route('attribute-values.index'));

        $response->assertStatus(200);
        $response->assertViewHas('attributeValues', function ($attributeValues) {
            return $attributeValues->count() === 20;  // Default pagination
        });
    }

    public function test_attribute_value_show_page_with_products(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $attributeValue->products()->attach([$product1->id, $product2->id]);

        $response = $this->get(route('attribute-values.show', $attributeValue));

        $response->assertStatus(200);
        $response->assertSee($product1->getDisplayName());
        $response->assertSee($product2->getDisplayName());
    }

    public function test_attribute_value_show_page_with_variants(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $product = Product::factory()->create();
        $variant = $product->variants()->create([
            'sku' => 'TEST-VARIANT',
            'price' => 99.99,
            'stock_quantity' => 10,
        ]);
        $attributeValue->variants()->attach($variant->id);

        $response = $this->get(route('attribute-values.show', $attributeValue));

        $response->assertStatus(200);
        $response->assertSee($variant->sku);
    }

    public function test_attribute_value_show_page_with_meta_data(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'meta_data' => [
                'created_by' => 'admin',
                'version' => '1.0',
                'tags' => ['red', 'color'],
            ],
        ]);

        $response = $this->get(route('attribute-values.show', $attributeValue));

        $response->assertStatus(200);
        $response->assertSee('created_by');
        $response->assertSee('admin');
        $response->assertSee('version');
        $response->assertSee('1.0');
    }

    public function test_attribute_value_show_page_with_color(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'color_code' => '#FF0000',
        ]);

        $response = $this->get(route('attribute-values.show', $attributeValue));

        $response->assertStatus(200);
        $response->assertSee('#FF0000');
    }

    public function test_attribute_value_show_page_with_status_badges(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'is_enabled' => true,
            'is_required' => true,
            'is_default' => true,
        ]);

        $response = $this->get(route('attribute-values.show', $attributeValue));

        $response->assertStatus(200);
        $response->assertSee(__('attributes.enabled'));
        $response->assertSee(__('attributes.required'));
        $response->assertSee(__('attributes.default'));
    }
}
