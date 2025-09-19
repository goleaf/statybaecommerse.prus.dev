<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_attributes_index_page_loads_successfully(): void
    {
        $response = $this->get(route('frontend.attributes.index'));

        $response->assertStatus(200);
        $response->assertViewIs('attributes.index');
    }

    public function test_attributes_index_page_with_filters(): void
    {
        Attribute::factory()->create(['type' => 'text', 'group_name' => 'basic_info']);
        Attribute::factory()->create(['type' => 'select', 'group_name' => 'technical_specs']);

        $response = $this->get(route('frontend.attributes.index', [
            'type' => 'text',
            'group' => 'basic_info',
            'search' => 'test'
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('attributes.index');
    }

    public function test_attribute_show_page_loads_successfully(): void
    {
        $attribute = Attribute::factory()->create();
        AttributeValue::factory()->count(3)->create(['attribute_id' => $attribute->id]);

        $response = $this->get(route('frontend.attributes.show', $attribute));

        $response->assertStatus(200);
        $response->assertViewIs('attributes.show');
        $response->assertViewHas('attribute', $attribute);
    }

    public function test_attribute_filter_products_endpoint(): void
    {
        $attribute = Attribute::factory()->create(['type' => 'select']);
        $value = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);
        $product = Product::factory()->create();

        $attribute->products()->attach($product->id);

        $response = $this->get(route('frontend.attributes.filter', [
            'attributes' => [$attribute->id => [$value->id]]
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('products.filtered');
    }

    public function test_get_attribute_values_api_endpoint(): void
    {
        $attribute = Attribute::factory()->create();
        AttributeValue::factory()->count(3)->create(['attribute_id' => $attribute->id]);

        $response = $this->get(route('frontend.attributes.api.values', [
            'attribute_id' => $attribute->id
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_get_attribute_values_api_endpoint_without_attribute_id(): void
    {
        $response = $this->get(route('frontend.attributes.api.values'));

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_get_attribute_statistics_api_endpoint(): void
    {
        $attribute = Attribute::factory()->create();
        $product = Product::factory()->create();
        $attribute->products()->attach($product->id);
        AttributeValue::factory()->count(2)->create(['attribute_id' => $attribute->id]);

        $response = $this->get(route('frontend.attributes.api.statistics', [
            'attribute_id' => $attribute->id
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'usage_count',
            'values_count',
            'enabled_values_count',
            'popularity_score',
            'average_values_per_product',
            'status',
            'status_color',
            'status_label'
        ]);
    }

    public function test_get_attribute_statistics_api_endpoint_without_attribute_id(): void
    {
        $response = $this->get(route('frontend.attributes.api.statistics'));

        $response->assertStatus(200);
        $response->assertJson(['error' => 'Attribute ID is required']);
    }

    public function test_get_attribute_groups_api_endpoint(): void
    {
        Attribute::factory()->create(['group_name' => 'basic_info']);
        Attribute::factory()->create(['group_name' => 'technical_specs']);
        Attribute::factory()->create(['group_name' => null]);

        $response = $this->get(route('frontend.attributes.api.groups'));

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_get_attribute_types_api_endpoint(): void
    {
        Attribute::factory()->create(['type' => 'text']);
        Attribute::factory()->create(['type' => 'select']);
        Attribute::factory()->create(['type' => 'number']);

        $response = $this->get(route('frontend.attributes.api.types'));

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_search_attributes_api_endpoint(): void
    {
        Attribute::factory()->create(['name' => 'Color Attribute', 'type' => 'select']);
        Attribute::factory()->create(['name' => 'Size Attribute', 'type' => 'text']);
        Attribute::factory()->create(['name' => 'Material Attribute', 'type' => 'select']);

        $response = $this->get(route('frontend.attributes.api.search', [
            'q' => 'Color',
            'type' => 'select'
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_search_attributes_api_endpoint_without_query(): void
    {
        Attribute::factory()->count(5)->create();

        $response = $this->get(route('frontend.attributes.api.search'));

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    public function test_get_attribute_comparison_api_endpoint(): void
    {
        $attribute1 = Attribute::factory()->create(['name' => 'Color']);
        $attribute2 = Attribute::factory()->create(['name' => 'Size']);

        $response = $this->get(route('frontend.attributes.api.compare', [
            'attribute_ids' => [$attribute1->id, $attribute2->id]
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_get_attribute_comparison_api_endpoint_with_insufficient_attributes(): void
    {
        $attribute = Attribute::factory()->create();

        $response = $this->get(route('frontend.attributes.api.compare', [
            'attribute_ids' => [$attribute->id]
        ]));

        $response->assertStatus(200);
        $response->assertJson(['error' => 'At least 2 attribute IDs are required']);
    }

    public function test_attributes_index_page_shows_attributes(): void
    {
        $attribute1 = Attribute::factory()->create(['name' => 'Color', 'is_enabled' => true, 'is_visible' => true]);
        $attribute2 = Attribute::factory()->create(['name' => 'Size', 'is_enabled' => true, 'is_visible' => true]);
        $attribute3 = Attribute::factory()->create(['name' => 'Material', 'is_enabled' => false, 'is_visible' => true]);

        $response = $this->get(route('frontend.attributes.index'));

        $response->assertStatus(200);
        $response->assertSee('Color');
        $response->assertSee('Size');
        $response->assertDontSee('Material'); // Should not see disabled attributes
    }

    public function test_attribute_show_page_shows_related_attributes(): void
    {
        $attribute = Attribute::factory()->create(['group_name' => 'basic_info']);
        $relatedAttribute = Attribute::factory()->create(['group_name' => 'basic_info']);

        $response = $this->get(route('frontend.attributes.show', $attribute));

        $response->assertStatus(200);
        $response->assertViewHas('relatedAttributes');
    }

    public function test_attribute_filter_with_price_range(): void
    {
        $product1 = Product::factory()->create(['price' => 100]);
        $product2 = Product::factory()->create(['price' => 200]);
        $product3 = Product::factory()->create(['price' => 300]);

        $response = $this->get(route('frontend.attributes.filter', [
            'price_min' => 150,
            'price_max' => 250
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('products.filtered');
    }

    public function test_attribute_filter_with_search(): void
    {
        $product1 = Product::factory()->create(['name' => 'Red T-Shirt']);
        $product2 = Product::factory()->create(['name' => 'Blue Jeans']);

        $response = $this->get(route('frontend.attributes.filter', [
            'search' => 'Red'
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('products.filtered');
    }

    public function test_attributes_api_endpoints_return_json(): void
    {
        $attribute = Attribute::factory()->create();

        $endpoints = [
            'frontend.attributes.api.values',
            'frontend.attributes.api.statistics',
            'frontend.attributes.api.groups',
            'frontend.attributes.api.types',
            'frontend.attributes.api.search',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->get(route($endpoint));
            $response->assertStatus(200);
            $response->assertHeader('content-type', 'application/json');
        }
    }

    public function test_attribute_show_page_with_products(): void
    {
        $attribute = Attribute::factory()->create();
        $product = Product::factory()->create(['is_visible' => true, 'published_at' => now()]);
        $attribute->products()->attach($product->id);

        $response = $this->get(route('frontend.attributes.show', $attribute));

        $response->assertStatus(200);
        $response->assertViewHas('attribute');
    }

    public function test_attribute_show_page_with_values(): void
    {
        $attribute = Attribute::factory()->create();
        AttributeValue::factory()->count(3)->create([
            'attribute_id' => $attribute->id,
            'is_enabled' => true
        ]);
        AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'is_enabled' => false
        ]);

        $response = $this->get(route('frontend.attributes.show', $attribute));

        $response->assertStatus(200);
        $response->assertViewHas('attribute');
    }
}
