<?php declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_attributes_index_page_loads_successfully(): void
    {
        $response = $this->get(route('frontend.attributes.index'));

        $response->assertStatus(200);
        $response->assertViewIs('attributes.index');
        $response->assertViewHas(['attributes', 'types', 'groups']);
    }

    public function test_attributes_index_shows_attributes(): void
    {
        $attribute = Attribute::factory()->create([
            'name' => 'Test Attribute',
            'is_enabled' => true,
            'is_visible' => true,
        ]);

        $response = $this->get(route('frontend.attributes.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Attribute');
    }

    public function test_attributes_index_filters_by_type(): void
    {
        $textAttribute = Attribute::factory()->create(['type' => 'text', 'is_enabled' => true, 'is_visible' => true]);
        $selectAttribute = Attribute::factory()->create(['type' => 'select', 'is_enabled' => true, 'is_visible' => true]);

        $response = $this->get(route('frontend.attributes.index', ['type' => 'text']));

        $response->assertStatus(200);
        $response->assertSee($textAttribute->name);
        $response->assertDontSee($selectAttribute->name);
    }

    public function test_attributes_index_filters_by_group(): void
    {
        $basicAttribute = Attribute::factory()->create(['group_name' => 'basic_info', 'is_enabled' => true, 'is_visible' => true]);
        $techAttribute = Attribute::factory()->create(['group_name' => 'technical_specs', 'is_enabled' => true, 'is_visible' => true]);

        $response = $this->get(route('frontend.attributes.index', ['group' => 'basic_info']));

        $response->assertStatus(200);
        $response->assertSee($basicAttribute->name);
        $response->assertDontSee($techAttribute->name);
    }

    public function test_attributes_index_searches_by_name(): void
    {
        $colorAttribute = Attribute::factory()->create(['name' => 'Color Attribute', 'is_enabled' => true, 'is_visible' => true]);
        $sizeAttribute = Attribute::factory()->create(['name' => 'Size Attribute', 'is_enabled' => true, 'is_visible' => true]);

        $response = $this->get(route('frontend.attributes.index', ['search' => 'Color']));

        $response->assertStatus(200);
        $response->assertSee($colorAttribute->name);
        $response->assertDontSee($sizeAttribute->name);
    }

    public function test_attributes_index_only_shows_enabled_and_visible_attributes(): void
    {
        $enabledAttribute = Attribute::factory()->create(['is_enabled' => true, 'is_visible' => true]);
        $disabledAttribute = Attribute::factory()->create(['is_enabled' => false, 'is_visible' => true]);
        $hiddenAttribute = Attribute::factory()->create(['is_enabled' => true, 'is_visible' => false]);

        $response = $this->get(route('frontend.attributes.index'));

        $response->assertStatus(200);
        $response->assertSee($enabledAttribute->name);
        $response->assertDontSee($disabledAttribute->name);
        $response->assertDontSee($hiddenAttribute->name);
    }

    public function test_attribute_show_page_loads_successfully(): void
    {
        $attribute = Attribute::factory()->create([
            'name' => 'Test Attribute',
            'is_enabled' => true,
            'is_visible' => true,
        ]);

        $response = $this->get(route('frontend.attributes.show', $attribute));

        $response->assertStatus(200);
        $response->assertViewIs('attributes.show');
        $response->assertViewHas(['attribute', 'relatedAttributes']);
        $response->assertSee($attribute->name);
    }

    public function test_attribute_show_page_displays_attribute_values(): void
    {
        $attribute = Attribute::factory()->create(['is_enabled' => true, 'is_visible' => true]);
        $value1 = AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'value' => 'Red', 'is_enabled' => true]);
        $value2 = AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'value' => 'Blue', 'is_enabled' => true]);

        $response = $this->get(route('frontend.attributes.show', $attribute));

        $response->assertStatus(200);
        $response->assertSee('Red');
        $response->assertSee('Blue');
    }

    public function test_attribute_show_page_displays_related_attributes(): void
    {
        $groupName = 'basic_info';
        $mainAttribute = Attribute::factory()->create(['group_name' => $groupName, 'is_enabled' => true, 'is_visible' => true]);
        $relatedAttribute = Attribute::factory()->create(['group_name' => $groupName, 'is_enabled' => true, 'is_visible' => true]);

        $response = $this->get(route('frontend.attributes.show', $mainAttribute));

        $response->assertStatus(200);
        $response->assertSee($relatedAttribute->name);
    }

    public function test_attribute_show_page_displays_products_using_attribute(): void
    {
        $attribute = Attribute::factory()->create(['is_enabled' => true, 'is_visible' => true]);
        $product = Product::factory()->create(['is_enabled' => true, 'is_published' => true]);

        $attribute->products()->attach($product->id);

        $response = $this->get(route('frontend.attributes.show', $attribute));

        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    public function test_attribute_filter_endpoint_returns_json(): void
    {
        $attribute = Attribute::factory()->create(['is_enabled' => true, 'is_visible' => true]);
        $value = AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'is_enabled' => true]);

        $response = $this->get(route('frontend.attributes.values', ['attribute_id' => $attribute->id]));

        $response->assertStatus(200);
        $response->assertJson([
            [
                'id' => $value->id,
                'value' => $value->value,
                'display_value' => $value->display_value ?: $value->value,
                'color' => $value->color,
            ]
        ]);
    }

    public function test_attribute_filter_endpoint_returns_empty_array_for_invalid_attribute(): void
    {
        $response = $this->get(route('frontend.attributes.values', ['attribute_id' => 999]));

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_attribute_filter_endpoint_returns_empty_array_without_attribute_id(): void
    {
        $response = $this->get(route('frontend.attributes.values'));

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_attributes_index_pagination_works(): void
    {
        // Create more attributes than the pagination limit
        Attribute::factory()->count(25)->create(['is_enabled' => true, 'is_visible' => true]);

        $response = $this->get(route('frontend.attributes.index'));

        $response->assertStatus(200);
        $response->assertViewHas('attributes');

        $attributes = $response->viewData('attributes');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $attributes);
    }

    public function test_attributes_index_shows_empty_state_when_no_attributes(): void
    {
        $response = $this->get(route('frontend.attributes.index'));

        $response->assertStatus(200);
        $response->assertSee(__('attributes.no_attributes_found'));
    }

    public function test_attribute_show_page_handles_missing_attribute(): void
    {
        $response = $this->get(route('frontend.attributes.show', 999));

        $response->assertStatus(404);
    }

    public function test_attribute_show_page_only_shows_enabled_values(): void
    {
        $attribute = Attribute::factory()->create(['is_enabled' => true, 'is_visible' => true]);
        $enabledValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'is_enabled' => true, 'value' => 'Enabled']);
        $disabledValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'is_enabled' => false, 'value' => 'Disabled']);

        $response = $this->get(route('frontend.attributes.show', $attribute));

        $response->assertStatus(200);
        $response->assertSee('Enabled');
        $response->assertDontSee('Disabled');
    }

    public function test_attribute_show_page_only_shows_published_products(): void
    {
        $attribute = Attribute::factory()->create(['is_enabled' => true, 'is_visible' => true]);
        $publishedProduct = Product::factory()->create(['is_enabled' => true, 'is_published' => true]);
        $unpublishedProduct = Product::factory()->create(['is_enabled' => true, 'is_published' => false]);

        $attribute->products()->attach([$publishedProduct->id, $unpublishedProduct->id]);

        $response = $this->get(route('frontend.attributes.show', $attribute));

        $response->assertStatus(200);
        $response->assertSee($publishedProduct->name);
        $response->assertDontSee($unpublishedProduct->name);
    }
}
