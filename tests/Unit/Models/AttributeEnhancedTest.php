<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AttributeEnhancedTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_scope_disabled(): void
    {
        Attribute::factory()->create(['is_enabled' => true, 'is_active' => true, 'is_visible' => true]);
        Attribute::factory()->create(['is_enabled' => false, 'is_active' => false, 'is_visible' => true]);

        $disabledAttributes = Attribute::withoutGlobalScopes()->disabled()->get();

        $this->assertCount(1, $disabledAttributes);
        $first = $disabledAttributes->first();
        $this->assertNotNull($first);
        $this->assertFalse($first->is_enabled);
    }

    public function test_attribute_scope_optional(): void
    {
        Attribute::factory()->create(['is_required' => true, 'is_enabled' => true, 'is_active' => true, 'is_visible' => true]);
        Attribute::factory()->create(['is_required' => false, 'is_enabled' => true, 'is_active' => true, 'is_visible' => true]);

        $optionalAttributes = Attribute::optional()->get();

        $this->assertCount(1, $optionalAttributes);
        $first = $optionalAttributes->first();
        $this->assertNotNull($first);
        $this->assertFalse($first->is_required);
    }

    public function test_attribute_factory_required_state(): void
    {
        $attribute = Attribute::factory()->required()->create();

        $this->assertTrue($attribute->is_required);
    }

    public function test_attribute_factory_optional_state(): void
    {
        $attribute = Attribute::factory()->optional()->create();

        $this->assertFalse($attribute->is_required);
    }

    public function test_attribute_factory_filterable_state(): void
    {
        $attribute = Attribute::factory()->filterable()->create();

        $this->assertTrue($attribute->is_filterable);
    }

    public function test_attribute_factory_searchable_state(): void
    {
        $attribute = Attribute::factory()->searchable()->create();

        $this->assertTrue($attribute->is_searchable);
    }

    public function test_attribute_factory_sortable_state(): void
    {
        $attribute = Attribute::factory()->sortable()->create();

        $this->assertTrue($attribute->is_sortable);
    }

    public function test_attribute_factory_visible_state(): void
    {
        $attribute = Attribute::factory()->visible()->create();

        $this->assertTrue($attribute->is_visible);
    }

    public function test_attribute_factory_hidden_state(): void
    {
        $attribute = Attribute::factory()->hidden()->create();

        $this->assertFalse($attribute->is_visible);
    }

    public function test_attribute_factory_editable_state(): void
    {
        $attribute = Attribute::factory()->editable()->create();

        $this->assertTrue($attribute->is_editable);
    }

    public function test_attribute_factory_readonly_state(): void
    {
        $attribute = Attribute::factory()->readonly()->create();

        $this->assertFalse($attribute->is_editable);
    }

    public function test_attribute_factory_enabled_state(): void
    {
        $attribute = Attribute::factory()->enabled()->create();

        $this->assertTrue($attribute->is_enabled);
        $this->assertTrue($attribute->is_active);
    }

    public function test_attribute_factory_disabled_state(): void
    {
        $attribute = Attribute::factory()->disabled()->create();

        $this->assertFalse($attribute->is_enabled);
        $this->assertFalse($attribute->is_active);
    }

    public function test_attribute_factory_text_state(): void
    {
        $attribute = Attribute::factory()->text()->create();

        $this->assertSame('text', $attribute->type);
    }

    public function test_attribute_factory_number_state(): void
    {
        $attribute = Attribute::factory()->number()->create();

        $this->assertSame('number', $attribute->type);
        $this->assertNotNull($attribute->min_value);
        $this->assertNotNull($attribute->max_value);
        $this->assertNotNull($attribute->step_value);
    }

    public function test_attribute_factory_boolean_state(): void
    {
        $attribute = Attribute::factory()->boolean()->create();

        $this->assertSame('boolean', $attribute->type);
        $this->assertNotNull($attribute->default_value);
    }

    public function test_attribute_factory_select_state(): void
    {
        $attribute = Attribute::factory()->select()->create();

        $this->assertSame('select', $attribute->type);
    }

    public function test_attribute_factory_multiselect_state(): void
    {
        $attribute = Attribute::factory()->multiselect()->create();

        $this->assertSame('multiselect', $attribute->type);
    }

    public function test_attribute_factory_color_state(): void
    {
        $attribute = Attribute::factory()->color()->create();

        $this->assertSame('color', $attribute->type);
        $this->assertNotNull($attribute->default_value);
    }

    public function test_attribute_factory_date_state(): void
    {
        $attribute = Attribute::factory()->date()->create();

        $this->assertSame('date', $attribute->type);
    }

    public function test_attribute_factory_textarea_state(): void
    {
        $attribute = Attribute::factory()->textarea()->create();

        $this->assertSame('textarea', $attribute->type);
        $this->assertNotNull($attribute->max_length);
    }

    public function test_attribute_factory_file_state(): void
    {
        $attribute = Attribute::factory()->file()->create();

        $this->assertSame('file', $attribute->type);
    }

    public function test_attribute_factory_image_state(): void
    {
        $attribute = Attribute::factory()->image()->create();

        $this->assertSame('image', $attribute->type);
    }

    public function test_attribute_factory_in_group_state(): void
    {
        $attribute = Attribute::factory()->inGroup('technical_specs')->create();

        $this->assertSame('technical_specs', $attribute->group_name);
    }

    public function test_attribute_factory_for_category_state(): void
    {
        $attribute = Attribute::factory()->forCategory(5)->create();

        $this->assertSame(5, $attribute->category_id);
    }

    public function test_attribute_factory_combined_states(): void
    {
        $attribute = Attribute::factory()
            ->text()
            ->required()
            ->filterable()
            ->searchable()
            ->inGroup('basic_info')
            ->create();

        $this->assertSame('text', $attribute->type);
        $this->assertTrue($attribute->is_required);
        $this->assertTrue($attribute->is_filterable);
        $this->assertTrue($attribute->is_searchable);
        $this->assertSame('basic_info', $attribute->group_name);
    }

    public function test_attribute_is_date_type(): void
    {
        $dateAttribute = Attribute::factory()->date()->create();
        $textAttribute = Attribute::factory()->text()->create();

        $this->assertTrue($dateAttribute->isDateType());
        $this->assertFalse($textAttribute->isDateType());
    }

    public function test_attribute_is_file_type(): void
    {
        $fileAttribute = Attribute::factory()->file()->create();
        $imageAttribute = Attribute::factory()->image()->create();
        $textAttribute = Attribute::factory()->text()->create();

        $this->assertTrue($fileAttribute->isFileType());
        $this->assertTrue($imageAttribute->isFileType());
        $this->assertFalse($textAttribute->isFileType());
    }

    public function test_attribute_can_have_multiple_values(): void
    {
        $multiselectAttribute = Attribute::factory()->multiselect()->create();
        $fileAttribute = Attribute::factory()->file()->create();
        $textAttribute = Attribute::factory()->text()->create();

        $this->assertTrue($multiselectAttribute->canHaveMultipleValues());
        $this->assertTrue($fileAttribute->canHaveMultipleValues());
        $this->assertFalse($textAttribute->canHaveMultipleValues());
    }

    public function test_attribute_get_default_value_for_type(): void
    {
        $booleanAttribute = Attribute::factory()->boolean()->create();
        $numberAttribute = Attribute::factory()->number()->create();
        $textAttribute = Attribute::factory()->text()->create();
        $colorAttribute = Attribute::factory()->color()->create();
        $multiselectAttribute = Attribute::factory()->multiselect()->create();

        $this->assertFalse($booleanAttribute->getDefaultValueForType());
        $this->assertSame(0, $numberAttribute->getDefaultValueForType());
        $this->assertSame('', $textAttribute->getDefaultValueForType());
        $this->assertSame('#000000', $colorAttribute->getDefaultValueForType());
        $this->assertSame([], $multiselectAttribute->getDefaultValueForType());
    }

    public function test_attribute_formatted_type_accessor(): void
    {
        $textAttribute = Attribute::factory()->text()->create();
        $numberAttribute = Attribute::factory()->number()->create();
        $booleanAttribute = Attribute::factory()->boolean()->create();
        $selectAttribute = Attribute::factory()->select()->create();
        $multiselectAttribute = Attribute::factory()->multiselect()->create();

        $this->assertSame('Text', $textAttribute->formatted_type);
        $this->assertSame('Number', $numberAttribute->formatted_type);
        $this->assertSame('Boolean', $booleanAttribute->formatted_type);
        $this->assertSame('Select', $selectAttribute->formatted_type);
        $this->assertSame('Multi Select', $multiselectAttribute->formatted_type);
    }

    public function test_attribute_type_icon_accessor(): void
    {
        $textAttribute = Attribute::factory()->text()->create();
        $numberAttribute = Attribute::factory()->number()->create();
        $booleanAttribute = Attribute::factory()->boolean()->create();
        $colorAttribute = Attribute::factory()->color()->create();

        $this->assertStringContainsString('heroicon', $textAttribute->type_icon);
        $this->assertStringContainsString('heroicon', $numberAttribute->type_icon);
        $this->assertStringContainsString('heroicon', $booleanAttribute->type_icon);
        $this->assertStringContainsString('heroicon', $colorAttribute->type_icon);
    }

    public function test_attribute_type_color_accessor(): void
    {
        $textAttribute = Attribute::factory()->text()->create();
        $numberAttribute = Attribute::factory()->number()->create();
        $booleanAttribute = Attribute::factory()->boolean()->create();

        $this->assertIsString($textAttribute->type_color);
        $this->assertIsString($numberAttribute->type_color);
        $this->assertIsString($booleanAttribute->type_color);
    }

    public function test_attribute_status_badge_accessor(): void
    {
        $disabledAttribute = Attribute::factory()->disabled()->create();
        $requiredAttribute = Attribute::factory()->required()->enabled()->create(['is_filterable' => false]);
        $filterableAttribute = Attribute::factory()->filterable()->enabled()->create(['is_required' => false]);
        $standardAttribute = Attribute::factory()->enabled()->create(['is_required' => false, 'is_filterable' => false]);

        $this->assertSame('disabled', $disabledAttribute->status_badge);
        $this->assertSame('required', $requiredAttribute->status_badge);
        $this->assertSame('filterable', $filterableAttribute->status_badge);
        $this->assertSame('standard', $standardAttribute->status_badge);
    }

    public function test_attribute_status_color_accessor(): void
    {
        $disabledAttribute = Attribute::factory()->disabled()->create();
        $requiredAttribute = Attribute::factory()->required()->enabled()->create(['is_filterable' => false]);
        $filterableAttribute = Attribute::factory()->filterable()->enabled()->create(['is_required' => false]);
        $standardAttribute = Attribute::factory()->enabled()->create(['is_required' => false, 'is_filterable' => false]);

        $this->assertSame('gray', $disabledAttribute->status_color);
        $this->assertSame('red', $requiredAttribute->status_color);
        $this->assertSame('blue', $filterableAttribute->status_color);
        $this->assertSame('green', $standardAttribute->status_color);
    }

    public function test_attribute_display_name_accessor(): void
    {
        $attribute = Attribute::factory()->create(['name' => 'Test Attribute']);

        $this->assertSame('Test Attribute', $attribute->display_name);
    }

    public function test_attribute_formatted_description_accessor(): void
    {
        $attribute = Attribute::factory()->create(['description' => '<p>Test description</p>']);

        $this->assertSame('Test description', $attribute->formatted_description);
    }

    public function test_attribute_validation_rules_array_accessor(): void
    {
        $attribute = Attribute::factory()->create([
            'validation_rules' => ['required', 'min:3', 'max:255'],
        ]);

        $this->assertIsArray($attribute->validation_rules_array);
        $this->assertContains('required', $attribute->validation_rules_array);
        $this->assertContains('min:3', $attribute->validation_rules_array);
    }

    public function test_attribute_meta_data_array_accessor(): void
    {
        $attribute = Attribute::factory()->create([
            'meta_data' => ['unit' => 'cm', 'precision' => 2],
        ]);

        $this->assertIsArray($attribute->meta_data_array);
        $this->assertArrayHasKey('unit', $attribute->meta_data_array);
        $this->assertSame('cm', $attribute->meta_data_array['unit']);
    }
}
