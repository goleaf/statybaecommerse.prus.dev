<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Attribute;
use App\Models\Translations\AttributeTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_can_be_created(): void
    {
        $attribute = Attribute::factory()->create([
            'name' => 'Test Attribute',
            'slug' => 'test-attribute',
            'type' => 'text',
            'description' => 'Test description',
            'is_required' => true,
            'is_filterable' => true,
            'is_searchable' => false,
            'is_visible' => true,
            'is_enabled' => true,
            'sort_order' => 1,
            'group_name' => 'test-group',
        ]);

        $this->assertInstanceOf(Attribute::class, $attribute);
        $this->assertEquals('Test Attribute', $attribute->name);
        $this->assertEquals('test-attribute', $attribute->slug);
        $this->assertEquals('text', $attribute->type);
        $this->assertEquals('Test description', $attribute->description);
        $this->assertTrue($attribute->is_required);
        $this->assertTrue($attribute->is_filterable);
        $this->assertFalse($attribute->is_searchable);
        $this->assertTrue($attribute->is_visible);
        $this->assertTrue($attribute->is_enabled);
        $this->assertEquals(1, $attribute->sort_order);
        $this->assertEquals('test-group', $attribute->group_name);
    }

    public function test_attribute_translation_methods(): void
    {
        $attribute = Attribute::factory()->create(['name' => 'Original Name']);
        
        // Test translation methods
        $this->assertEquals('Original Name', $attribute->getTranslatedName());
        $this->assertEquals($attribute->description, $attribute->getTranslatedDescription());
        $this->assertEquals($attribute->slug, $attribute->getTranslatedSlug());
        
        // Test with translation
        $attribute->updateTranslation('en', [
            'name' => 'English Name',
            'description' => 'English Description',
            'slug' => 'english-slug',
        ]);
        
        $this->assertEquals('English Name', $attribute->getTranslatedName('en'));
        $this->assertEquals('English Description', $attribute->getTranslatedDescription('en'));
        $this->assertEquals('english-slug', $attribute->getTranslatedSlug('en'));
    }

    public function test_attribute_scopes(): void
    {
        // Clear any existing attributes first
        Attribute::query()->delete();

        // Create test attributes with specific attributes
        $enabledAttribute = Attribute::factory()->create(['is_enabled' => true]);
        $disabledAttribute = Attribute::factory()->create(['is_enabled' => false]);
        $requiredAttribute = Attribute::factory()->create(['is_required' => true]);
        $filterableAttribute = Attribute::factory()->create(['is_filterable' => true]);
        $searchableAttribute = Attribute::factory()->create(['is_searchable' => true]);
        $orderedAttribute1 = Attribute::factory()->create(['sort_order' => 2]);
        $orderedAttribute2 = Attribute::factory()->create(['sort_order' => 1]);

        // Test enabled scope
        $enabledAttributes = Attribute::enabled()->get();
        $this->assertCount(1, $enabledAttributes);
        $this->assertEquals($enabledAttribute->id, $enabledAttributes->first()->id);

        // Test required scope
        $requiredAttributes = Attribute::required()->get();
        $this->assertCount(1, $requiredAttributes);
        $this->assertEquals($requiredAttribute->id, $requiredAttributes->first()->id);

        // Test filterable scope
        $filterableAttributes = Attribute::filterable()->get();
        $this->assertCount(1, $filterableAttributes);
        $this->assertEquals($filterableAttribute->id, $filterableAttributes->first()->id);

        // Test searchable scope
        $searchableAttributes = Attribute::searchable()->get();
        $this->assertCount(1, $searchableAttributes);
        $this->assertEquals($searchableAttribute->id, $searchableAttributes->first()->id);

        // Test ordered scope
        $orderedAttributes = Attribute::ordered()->get();
        $this->assertCount(2, $orderedAttributes);
        $this->assertEquals($orderedAttribute2->id, $orderedAttributes->first()->id); // sort_order = 1 comes first
    }

    public function test_attribute_helper_methods(): void
    {
        $attribute = Attribute::factory()->create([
            'type' => 'text',
            'name' => 'Test Attribute',
        ]);

        // Test type methods
        $this->assertTrue($attribute->isTextType());
        $this->assertFalse($attribute->isNumericType());
        $this->assertFalse($attribute->isBooleanType());
        $this->assertFalse($attribute->isSelectType());

        // Test full display name
        $displayName = $attribute->getFullDisplayName();
        $this->assertStringContainsString('Test Attribute', $displayName);
        $this->assertStringContainsString('Text', $displayName);

        // Test info methods
        $attributeInfo = $attribute->getAttributeInfo();
        $this->assertArrayHasKey('id', $attributeInfo);
        $this->assertArrayHasKey('name', $attributeInfo);
        $this->assertArrayHasKey('type', $attributeInfo);

        $technicalInfo = $attribute->getTechnicalInfo();
        $this->assertArrayHasKey('type', $technicalInfo);
        $this->assertArrayHasKey('default_value', $technicalInfo);

        $businessInfo = $attribute->getBusinessInfo();
        $this->assertArrayHasKey('usage_count', $businessInfo);
        $this->assertArrayHasKey('values_count', $businessInfo);

        $completeInfo = $attribute->getCompleteInfo();
        $this->assertArrayHasKey('translations', $completeInfo);
        $this->assertArrayHasKey('has_translations', $completeInfo);
    }

    public function test_attribute_translation_management(): void
    {
        $attribute = Attribute::factory()->create();

        // Test available locales (should be empty initially)
        $this->assertEmpty($attribute->getAvailableLocales());

        // Test has translation for
        $this->assertFalse($attribute->hasTranslationFor('en'));

        // Test get or create translation
        $translation = $attribute->getOrCreateTranslation('en');
        $this->assertInstanceOf(AttributeTranslation::class, $translation);
        $this->assertEquals('en', $translation->locale);

        // Test update translation
        $this->assertTrue($attribute->updateTranslation('en', [
            'name' => 'English Name',
            'description' => 'English Description',
        ]));

        // Test available locales now includes 'en'
        $this->assertContains('en', $attribute->getAvailableLocales());
        $this->assertTrue($attribute->hasTranslationFor('en'));

        // Test update multiple translations
        $this->assertTrue($attribute->updateTranslations([
            'lt' => [
                'name' => 'Lithuanian Name',
                'description' => 'Lithuanian Description',
            ],
        ]));

        $this->assertContains('lt', $attribute->getAvailableLocales());
    }

    public function test_attribute_relations(): void
    {
        $attribute = Attribute::factory()->create();

        // Test values relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $attribute->values());

        // Test products relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $attribute->products());

        // Test variants relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $attribute->variants());

        // Test category relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $attribute->category());
    }

    public function test_attribute_with_translations_scope(): void
    {
        $attribute = Attribute::factory()->create();
        $attribute->updateTranslation('en', ['name' => 'English Name']);

        $attributesWithTranslations = Attribute::withTranslations('en')->get();
        $this->assertCount(1, $attributesWithTranslations);
        
        $loadedAttribute = $attributesWithTranslations->first();
        $this->assertTrue($loadedAttribute->relationLoaded('translations'));
    }

    public function test_attribute_type_methods(): void
    {
        $textAttribute = Attribute::factory()->create(['type' => 'text']);
        $numberAttribute = Attribute::factory()->create(['type' => 'number']);
        $booleanAttribute = Attribute::factory()->create(['type' => 'boolean']);
        $selectAttribute = Attribute::factory()->create(['type' => 'select']);

        // Test text type
        $this->assertTrue($textAttribute->isTextType());
        $this->assertFalse($textAttribute->isNumericType());
        $this->assertFalse($textAttribute->isBooleanType());
        $this->assertFalse($textAttribute->isSelectType());

        // Test number type
        $this->assertFalse($numberAttribute->isTextType());
        $this->assertTrue($numberAttribute->isNumericType());
        $this->assertFalse($numberAttribute->isBooleanType());
        $this->assertFalse($numberAttribute->isSelectType());

        // Test boolean type
        $this->assertFalse($booleanAttribute->isTextType());
        $this->assertFalse($booleanAttribute->isNumericType());
        $this->assertTrue($booleanAttribute->isBooleanType());
        $this->assertFalse($booleanAttribute->isSelectType());

        // Test select type
        $this->assertFalse($selectAttribute->isTextType());
        $this->assertFalse($selectAttribute->isNumericType());
        $this->assertFalse($selectAttribute->isBooleanType());
        $this->assertTrue($selectAttribute->isSelectType());
    }

    public function test_attribute_statistics(): void
    {
        $attribute = Attribute::factory()->create();
        
        // Test statistics method
        $statistics = $attribute->getStatistics();
        $this->assertArrayHasKey('usage_count', $statistics);
        $this->assertArrayHasKey('values_count', $statistics);
        $this->assertArrayHasKey('popularity_score', $statistics);
        $this->assertArrayHasKey('status', $statistics);
        $this->assertArrayHasKey('status_color', $statistics);
        $this->assertArrayHasKey('status_label', $statistics);
        
        // Test individual statistic methods
        $this->assertIsInt($attribute->getUsageCount());
        $this->assertIsInt($attribute->getValuesCount());
        $this->assertIsInt($attribute->getEnabledValuesCount());
        $this->assertIsInt($attribute->getPopularityScore());
        $this->assertIsFloat($attribute->getAverageValuesPerProduct());
    }

    public function test_attribute_form_component_config(): void
    {
        $attribute = Attribute::factory()->create([
            'type' => 'select',
            'is_required' => true,
            'default_value' => 'default',
            'min_value' => 1,
            'max_value' => 100,
            'step_value' => 0.5,
        ]);

        $config = $attribute->getFormComponentConfig();
        
        $this->assertEquals('select', $config['type']);
        $this->assertEquals($attribute->name, $config['label']);
        $this->assertTrue($config['required']);
        $this->assertEquals('default', $config['default_value']);
        $this->assertEquals(1, $config['min_value']);
        $this->assertEquals(100, $config['max_value']);
        $this->assertEquals(0.5, $config['step_value']);
        $this->assertArrayHasKey('validation_rules', $config);
        $this->assertArrayHasKey('options', $config);
    }
}