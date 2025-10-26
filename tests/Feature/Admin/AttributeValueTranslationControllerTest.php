<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Translations\AttributeValueTranslation;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * AttributeValueTranslationControllerTest
 *
 * Feature tests covering the attribute value translation controller endpoints.
 */
final class AttributeValueTranslationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Authenticate a test user to satisfy the admin middleware stack.
        $user = User::factory()->create();
        $this->actingAs($user);

        // Ensure the pivot table carries the columns required by the controller contract in Sqlite test runs.
        Schema::table('attribute_value_translations', function (Blueprint $table): void {
            if (! Schema::hasColumn('attribute_value_translations', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('attribute_value_translations', 'meta_data')) {
                $table->json('meta_data')->nullable();
            }
        });
    }

    public function test_index_returns_translations(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->getKey(),
        ]);
        $attributeValue->translations()->delete();

        // Seed two translations to confirm ordering and payload shape.
        AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->getKey(),
            'locale'             => 'lt',
            'value'              => 'Vertė',
        ]);

        AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->getKey(),
            'locale'             => 'en',
            'value'              => 'Value',
        ]);

        $response = $this->getJson(route('admin.attribute-values.translations.index', [
            'attributeValue' => $attributeValue,
        ]));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment([
            'locale' => 'en',
            'value'  => 'Value',
        ]);
    }

    public function test_store_creates_translation(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->getKey(),
        ]);
        $attributeValue->translations()->delete();

        $payload = [
            'locale'      => 'en',
            'value'       => 'English Value',
            'description' => 'English description',
            'meta_data'   => ['source' => 'test'],
        ];

        $response = $this->postJson(route('admin.attribute-values.translations.store', [
            'attributeValue' => $attributeValue,
        ]), $payload);

        $response->assertCreated();
        $response->assertJsonFragment([
            'locale' => 'en',
            'value'  => 'English Value',
        ]);

        $this->assertDatabaseHas('attribute_value_translations', [
            'attribute_value_id' => $attributeValue->getKey(),
            'locale'             => 'en',
            'value'              => 'English Value',
        ]);
    }

    public function test_update_modifies_translation(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->getKey(),
        ]);
        $attributeValue->translations()->delete();

        $translation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->getKey(),
            'locale'             => 'en',
            'value'              => 'Original Value',
        ]);

        $response = $this->patchJson(route('admin.attribute-values.translations.update', [
            'attributeValue'            => $attributeValue,
            'attributeValueTranslation' => $translation,
        ]), [
            'value'       => 'Updated Value',
            'description' => 'Updated Description',
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'value' => 'Updated Value',
        ]);

        $this->assertDatabaseHas('attribute_value_translations', [
            'id'          => $translation->getKey(),
            'value'       => 'Updated Value',
            'description' => 'Updated Description',
        ]);
    }

    public function test_destroy_deletes_translation(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->getKey(),
        ]);
        $attributeValue->translations()->delete();

        $translation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $attributeValue->getKey(),
            'locale'             => 'en',
            'value'              => 'To be deleted',
        ]);

        $response = $this->deleteJson(route('admin.attribute-values.translations.destroy', [
            'attributeValue'            => $attributeValue,
            'attributeValueTranslation' => $translation,
        ]));

        $response->assertOk();

        $this->assertDatabaseMissing('attribute_value_translations', [
            'id' => $translation->getKey(),
        ]);
    }

    public function test_update_returns_not_found_for_mismatched_translation(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->getKey(),
        ]);
        $attributeValue->translations()->delete();
        $otherValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->getKey(),
        ]);
        $otherValue->translations()->delete();

        $translation = AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $otherValue->getKey(),
            'locale'             => 'en',
        ]);

        $response = $this->patchJson(route('admin.attribute-values.translations.update', [
            'attributeValue'            => $attributeValue,
            'attributeValueTranslation' => $translation,
        ]), [
            'value' => 'Updated Value',
        ]);

        $response->assertNotFound();
    }
}
