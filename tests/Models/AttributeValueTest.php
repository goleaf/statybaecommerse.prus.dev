<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Translations\AttributeValueTranslation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

final class AttributeValueTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_configuration_is_locked_down(): void
    {
        // Ensure mass-assignment protection covers every user facing column while avoiding accidental gaps.
        $model = new AttributeValue;

        $this->assertSame([
            'attribute_id',
            'value',
            'slug',
            'attribute_value_type',
            'valueable_type',
            'valueable_id',
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
            'is_searchable',
        ], $model->getFillable());
    }

    public function test_casts_configuration_matches_expectations(): void
    {
        // Validate cast definitions so that boolean and array flags keep behaving consistently.
        $model = new AttributeValue;

        $this->assertSame([
            'id'            => 'int',
            'sort_order'    => 'integer',
            'is_enabled'    => 'boolean',
            'is_active'     => 'boolean',
            'is_default'    => 'boolean',
            'is_searchable' => 'boolean',
            'metadata'      => 'array',
            'deleted_at'    => 'datetime',
        ], $model->getCasts());
    }

    public function test_relationships_are_configured(): void
    {
        // Confirm the relationship return types for IDE helpers and static analysers alike.
        $model = new AttributeValue;

        $this->assertInstanceOf(BelongsTo::class, $model->attribute());
        $this->assertInstanceOf(BelongsToMany::class, $model->products());
        $this->assertInstanceOf(BelongsToMany::class, $model->variants());
        $this->assertInstanceOf(MorphTo::class, $model->valueable());
    }

    public function test_translation_model_property_points_to_translation_class(): void
    {
        // Guard the private translation model mapping to keep translations in sync.
        $model = new AttributeValue;
        $reflection = new ReflectionClass($model);
        $property = $reflection->getProperty('translationModel');

        $this->assertSame(AttributeValueTranslation::class, $property->getValue($model));
    }

    public function test_scope_enabled_respects_flag(): void
    {
        // Build one enabled and one disabled entry to ensure the scope filters correctly.
        /** @var AttributeValue $enabled */
        $enabled = AttributeValue::factory()->create();
        /** @var AttributeValue $disabled */
        $disabled = AttributeValue::factory()->create(['is_enabled' => false]);

        $results = AttributeValue::query()
            ->withoutGlobalScope(EnabledScope::class)
            ->enabled()
            ->pluck('id');

        $this->assertTrue($results->contains($enabled->id));
        $this->assertFalse($results->contains($disabled->id));
    }

    public function test_scope_active_requires_active_flag(): void
    {
        // Mix active and inactive records and ensure the active scope trims inactive ones.
        /** @var AttributeValue $active */
        $active = AttributeValue::factory()->create();
        /** @var AttributeValue $inactive */
        $inactive = AttributeValue::factory()->create(['is_active' => false]);

        $results = AttributeValue::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->active()
            ->pluck('id');

        $this->assertTrue($results->contains($active->id));
        $this->assertFalse($results->contains($inactive->id));
    }

    public function test_scope_ordered_sorts_by_sort_order(): void
    {
        // Create explicit sort orders so the ordered scope can be asserted deterministically.
        /** @var AttributeValue $first */
        $first = AttributeValue::factory()->create(['sort_order' => 2]);
        /** @var AttributeValue $second */
        $second = AttributeValue::factory()->create(['sort_order' => 1]);

        $ordered = AttributeValue::query()
            ->withoutGlobalScopes()
            ->ordered()
            ->pluck('id')
            ->all();

        $this->assertSame([$second->id, $first->id], $ordered);
    }

    public function test_scope_ordered_by_name_prefers_display_value(): void
    {
        // Ensure the name-based ordering prefers display_value while falling back to the raw value column.
        /** @var AttributeValue $alpha */
        $alpha = AttributeValue::factory()->create([
            'display_value' => 'Alpha',
            'value'         => 'Zulu',
            'sort_order'    => 5,
        ]);
        /** @var AttributeValue $bravo */
        $bravo = AttributeValue::factory()->create([
            'display_value' => 'Bravo',
            'value'         => 'Bravo',
            'sort_order'    => 1,
        ]);
        /** @var AttributeValue $fallback */
        $fallback = AttributeValue::factory()->create([
            'display_value' => null,
            'value'         => 'Charlie',
            'sort_order'    => 3,
        ]);

        $ordered = AttributeValue::query()
            ->withoutGlobalScopes()
            ->orderedByName()
            ->pluck('id')
            ->all();

        $this->assertSame([$alpha->id, $bravo->id, $fallback->id], $ordered);
    }

    public function test_scope_for_attribute_filters_by_attribute_id(): void
    {
        // Create two attributes and confirm the scope isolates the intended foreign key.
        /** @var Attribute $attributeA */
        $attributeA = Attribute::factory()->create();
        /** @var Attribute $attributeB */
        $attributeB = Attribute::factory()->create();

        /** @var AttributeValue $matching */
        $matching = AttributeValue::factory()->create(['attribute_id' => (int) $attributeA->id]);
        AttributeValue::factory()->create(['attribute_id' => (int) $attributeB->id]);

        $ids = AttributeValue::query()
            ->withoutGlobalScopes()
            ->forAttribute($attributeA->id)
            ->pluck('id')
            ->all();

        $this->assertSame([$matching->id], $ids);
    }

    public function test_scope_by_value_matches_exact_string(): void
    {
        // The value scope must perform an exact match so we seed two distinct values.
        /** @var AttributeValue $needle */
        $needle = AttributeValue::factory()->create(['value' => 'Needle']);
        AttributeValue::factory()->create(['value' => 'Haystack']);

        $ids = AttributeValue::query()
            ->withoutGlobalScopes()
            ->byValue('Needle')
            ->pluck('id')
            ->all();

        $this->assertSame([$needle->id], $ids);
    }

    public function test_metadata_accessor_handles_json_payload(): void
    {
        // Persist metadata as JSON and check the accessor converts it back into an array.
        /** @var AttributeValue $value */
        $value = AttributeValue::factory()->make();

        // Seed the raw attribute directly to emulate the stored JSON payload without touching the database.
        $value->setRawAttributes(array_merge($value->getAttributes(), [
            'metadata' => json_encode(['foo' => 'bar'], JSON_THROW_ON_ERROR),
        ]), true);

        $this->assertSame(['foo' => 'bar'], $value->getAttribute('metadata'));
    }

    public function test_refresh_bypasses_global_scopes(): void
    {
        // Verify the custom refresh method rehydrates models even after disabling flags that global scopes would hide.
        /** @var AttributeValue $value */
        $value = AttributeValue::factory()->create();
        AttributeValue::withoutGlobalScopes()
            ->whereKey($value->getKey())
            ->update(['is_enabled' => false]);

        $value->refresh();

        $this->assertFalse($value->is_enabled);
    }
}
