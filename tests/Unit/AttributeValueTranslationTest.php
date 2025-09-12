<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Translations\AttributeValueTranslation;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeValueTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_value_translation_can_be_created(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translation = AttributeValueTranslation::create([
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'en',
            'value' => 'Red Color',
            'description' => 'A beautiful red color option',
            'meta_data' => ['hex' => '#FF0000', 'rgb' => '255,0,0'],
        ]);

        $this->assertDatabaseHas('attribute_value_translations', [
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'en',
            'value' => 'Red Color',
            'description' => 'A beautiful red color option',
        ]);

        $this->assertEquals('Red Color', $translation->value);
        $this->assertEquals('A beautiful red color option', $translation->description);
        $this->assertEquals(['hex' => '#FF0000', 'rgb' => '255,0,0'], $translation->meta_data);
    }

    public function test_attribute_value_translation_belongs_to_attribute_value(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);
        $translation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
        ]);

        $this->assertInstanceOf(AttributeValue::class, $translation->attributeValue);
        $this->assertEquals($attributeValue->id, $translation->attributeValue->id);
    }

    public function test_attribute_value_translation_casts_work_correctly(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translation = AttributeValueTranslation::create([
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'en',
            'value' => 'Test Value',
            'meta_data' => ['key' => 'value'],
        ]);

        $this->assertIsInt($translation->attribute_value_id);
        $this->assertIsArray($translation->meta_data);
        $this->assertEquals(['key' => 'value'], $translation->meta_data);
    }

    public function test_attribute_value_translation_fillable_attributes(): void
    {
        $translation = new AttributeValueTranslation();
        $fillable = $translation->getFillable();

        $this->assertContains('attribute_value_id', $fillable);
        $this->assertContains('locale', $fillable);
        $this->assertContains('value', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('meta_data', $fillable);
    }

    public function test_attribute_value_translation_scope_by_locale(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $enTranslation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'en',
        ]);

        $ltTranslation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'lt',
        ]);

        $enTranslations = AttributeValueTranslation::byLocale('en')->get();
        $ltTranslations = AttributeValueTranslation::byLocale('lt')->get();

        $this->assertTrue($enTranslations->contains($enTranslation));
        $this->assertFalse($enTranslations->contains($ltTranslation));
        $this->assertTrue($ltTranslations->contains($ltTranslation));
        $this->assertFalse($ltTranslations->contains($enTranslation));
    }

    public function test_attribute_value_translation_scope_by_attribute_value(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue1 = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);
        $attributeValue2 = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translation1 = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue1->id,
        ]);

        $translation2 = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue2->id,
        ]);

        $attributeValue1Translations = AttributeValueTranslation::byAttributeValue($attributeValue1->id)->get();

        $this->assertTrue($attributeValue1Translations->contains($translation1));
        $this->assertFalse($attributeValue1Translations->contains($translation2));
    }

    public function test_attribute_value_translation_scope_with_value(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translationWithValue = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'value' => 'Test Value',
        ]);

        $translationWithoutValue = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'value' => null,
        ]);

        $translationsWithValue = AttributeValueTranslation::withValue()->get();

        $this->assertTrue($translationsWithValue->contains($translationWithValue));
        $this->assertFalse($translationsWithValue->contains($translationWithoutValue));
    }

    public function test_attribute_value_translation_scope_with_description(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translationWithDescription = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'description' => 'Test Description',
        ]);

        $translationWithoutDescription = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'description' => null,
        ]);

        $translationsWithDescription = AttributeValueTranslation::withDescription()->get();

        $this->assertTrue($translationsWithDescription->contains($translationWithDescription));
        $this->assertFalse($translationsWithDescription->contains($translationWithoutDescription));
    }

    public function test_attribute_value_translation_accessor_formatted_value(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'value' => 'Test Value',
        ]);

        $this->assertEquals('Test Value', $translation->formatted_value);
    }

    public function test_attribute_value_translation_accessor_formatted_value_with_null(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'value' => null,
        ]);

        $this->assertEquals(__('attributes.untitled_value'), $translation->formatted_value);
    }

    public function test_attribute_value_translation_accessor_formatted_description(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'description' => 'Test Description',
        ]);

        $this->assertEquals('Test Description', $translation->formatted_description);
    }

    public function test_attribute_value_translation_accessor_formatted_description_with_null(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'description' => null,
        ]);

        $this->assertNull($translation->formatted_description);
    }

    public function test_attribute_value_translation_accessor_meta_data_array(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'meta_data' => ['key' => 'value'],
        ]);

        $this->assertEquals(['key' => 'value'], $translation->meta_data_array);
    }

    public function test_attribute_value_translation_accessor_meta_data_array_with_null(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'meta_data' => null,
        ]);

        $this->assertEquals([], $translation->meta_data_array);
    }

    public function test_attribute_value_translation_helper_has_value(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translationWithValue = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'value' => 'Test Value',
        ]);

        $translationWithoutValue = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'value' => null,
        ]);

        $this->assertTrue($translationWithValue->hasValue());
        $this->assertFalse($translationWithoutValue->hasValue());
    }

    public function test_attribute_value_translation_helper_has_description(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translationWithDescription = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'description' => 'Test Description',
        ]);

        $translationWithoutDescription = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'description' => null,
        ]);

        $this->assertTrue($translationWithDescription->hasDescription());
        $this->assertFalse($translationWithoutDescription->hasDescription());
    }

    public function test_attribute_value_translation_helper_has_meta_data(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translationWithMetaData = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'meta_data' => ['key' => 'value'],
        ]);

        $translationWithoutMetaData = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'meta_data' => null,
        ]);

        $this->assertTrue($translationWithMetaData->hasMetaData());
        $this->assertFalse($translationWithoutMetaData->hasMetaData());
    }

    public function test_attribute_value_translation_helper_is_empty(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $emptyTranslation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'value' => null,
            'description' => null,
            'meta_data' => null,
        ]);

        $nonEmptyTranslation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'value' => 'Test Value',
            'description' => null,
            'meta_data' => null,
        ]);

        $this->assertTrue($emptyTranslation->isEmpty());
        $this->assertFalse($nonEmptyTranslation->isEmpty());
    }

    public function test_attribute_value_translation_helper_is_complete(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $completeTranslation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'value' => 'Test Value',
        ]);

        $incompleteTranslation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'value' => null,
        ]);

        $this->assertTrue($completeTranslation->isComplete());
        $this->assertFalse($incompleteTranslation->isComplete());
    }

    public function test_attribute_value_translation_static_get_by_attribute_value_and_locale(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $translation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'en',
        ]);

        $foundTranslation = AttributeValueTranslation::getByAttributeValueAndLocale($attributeValue->id, 'en');
        $notFoundTranslation = AttributeValueTranslation::getByAttributeValueAndLocale($attributeValue->id, 'lt');

        $this->assertInstanceOf(AttributeValueTranslation::class, $foundTranslation);
        $this->assertEquals($translation->id, $foundTranslation->id);
        $this->assertNull($notFoundTranslation);
    }

    public function test_attribute_value_translation_static_get_or_create_for_attribute_value_and_locale(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        // First call should create
        $translation1 = AttributeValueTranslation::getOrCreateForAttributeValueAndLocale($attributeValue->id, 'en');
        $this->assertInstanceOf(AttributeValueTranslation::class, $translation1);
        $this->assertEquals('', $translation1->value);

        // Second call should return existing
        $translation2 = AttributeValueTranslation::getOrCreateForAttributeValueAndLocale($attributeValue->id, 'en');
        $this->assertEquals($translation1->id, $translation2->id);
    }

    public function test_attribute_value_translation_static_get_translations_for_attribute_value(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $enTranslation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'en',
        ]);

        $ltTranslation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'lt',
        ]);

        $translations = AttributeValueTranslation::getTranslationsForAttributeValue($attributeValue->id);

        $this->assertCount(2, $translations);
        $this->assertTrue($translations->contains($enTranslation));
        $this->assertTrue($translations->contains($ltTranslation));
    }

    public function test_attribute_value_translation_static_get_available_locales_for_attribute_value(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'en',
        ]);

        AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'lt',
        ]);

        $locales = AttributeValueTranslation::getAvailableLocalesForAttributeValue($attributeValue->id);

        $this->assertCount(2, $locales);
        $this->assertContains('en', $locales);
        $this->assertContains('lt', $locales);
    }

    public function test_attribute_value_translation_static_get_missing_locales_for_attribute_value(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->id,
            'locale' => 'en',
        ]);

        $supportedLocales = ['en', 'lt', 'de'];
        $missingLocales = AttributeValueTranslation::getMissingLocalesForAttributeValue($attributeValue->id, $supportedLocales);

        $this->assertCount(2, $missingLocales);
        $this->assertContains('lt', $missingLocales);
        $this->assertContains('de', $missingLocales);
        $this->assertNotContains('en', $missingLocales);
    }
}
