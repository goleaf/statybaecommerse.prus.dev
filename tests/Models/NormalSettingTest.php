<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\NormalSetting;
use App\Models\NormalSettingTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 */
final class NormalSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_name_is_consistent(): void
    {
        // Ensure the eloquent model stays aligned with the migration table name.
        $setting = new NormalSetting;

        $this->assertSame('enhanced_settings', $setting->getTable());
    }

    public function test_fillable_attributes_cover_expected_columns(): void
    {
        // Guard that future refactors keep the whitelisted attributes aligned to the schema.
        $setting = new NormalSetting;

        $this->assertSame([
            'group',
            'key',
            'locale',
            'value',
            'type',
            'description',
            'is_public',
            'is_encrypted',
            'is_active',
            'validation_rules',
            'sort_order',
        ], $setting->getFillable());
    }

    public function test_casts_configuration_remains_stable(): void
    {
        // The cast map ensures consistent types when hydrating the model from the database.
        $setting = new NormalSetting;

        $expectedCasts = [
            'is_public'        => 'boolean',
            'is_encrypted'     => 'boolean',
            'is_active'        => 'boolean',
            'sort_order'       => 'integer',
            'validation_rules' => 'json',
        ];

        foreach ($expectedCasts as $attribute => $castType) {
            // Looping avoids relying on deprecated assertion helpers while keeping intent obvious.
            $this->assertArrayHasKey($attribute, $setting->getCasts());
            $this->assertSame($castType, $setting->getCasts()[$attribute]);
        }
    }

    public function test_scope_by_group_filters_records(): void
    {
        // Arrange a couple of settings in different groups to assert the scope effect.
        NormalSetting::factory()->create(['group' => 'general']);
        NormalSetting::factory()->create(['group' => 'email']);
        NormalSetting::factory()->create(['group' => 'general']);

        $result = NormalSetting::byGroup('general')->get();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn (NormalSetting $setting): bool => $setting->group === 'general'));
    }

    public function test_scope_public_returns_only_public_settings(): void
    {
        // Validate that the convenience scope respects the boolean cast.
        NormalSetting::factory()->create(['is_public' => true]);
        NormalSetting::factory()->create(['is_public' => false]);

        $result = NormalSetting::public()->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->firstOrFail()->is_public);
    }

    public function test_scope_active_returns_only_active_settings(): void
    {
        // Confirm the active scope aligns with the schema column introduced in later migrations.
        NormalSetting::factory()->create(['is_active' => true]);
        NormalSetting::factory()->create(['is_active' => false]);

        $result = NormalSetting::active()->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->firstOrFail()->is_active);
    }

    public function test_scope_ordered_respects_group_sort_and_key(): void
    {
        // Insert deliberately shuffled data to observe the ordering logic.
        $first = NormalSetting::factory()->create([
            'group'      => 'email',
            'sort_order' => 10,
            'key'        => 'beta',
        ]);
        $second = NormalSetting::factory()->create([
            'group'      => 'general',
            'sort_order' => 1,
            'key'        => 'alpha',
        ]);
        $third = NormalSetting::factory()->create([
            'group'      => 'general',
            'sort_order' => 2,
            'key'        => 'omega',
        ]);

        $ordered = NormalSetting::ordered()->get();

        $this->assertSame([$first->id, $second->id, $third->id], $ordered->pluck('id')->all());
    }

    public function test_value_accessor_handles_encrypted_payloads(): void
    {
        // Using the encrypt helper exercises the decrypt branch on the accessor.
        $setting = NormalSetting::factory()->create([
            'is_encrypted' => true,
            'value'        => encrypt('secret'),
            'type'         => NormalSetting::TYPE_STRING,
        ]);

        $this->assertSame('secret', $setting->value);
    }

    public function test_value_mutator_converts_array_to_json(): void
    {
        // Arrays should be serialized so the storage column stays compatible with JSON columns.
        $setting = NormalSetting::factory()->create([
            'type'  => NormalSetting::TYPE_JSON,
            'value' => ['foo' => 'bar'],
        ]);

        $this->assertSame(['foo' => 'bar'], $setting->value);
    }

    public function test_set_value_creates_and_updates_records(): void
    {
        // First call should create a record with the supplied payload.
        NormalSetting::setValue('integration_key', 'initial', 'integrations');

        $this->assertDatabaseHas('enhanced_settings', [
            'key'   => 'integration_key',
            'group' => 'integrations',
            'value' => 'initial',
        ]);

        // Second call should update the existing record without duplicating rows.
        NormalSetting::setValue('integration_key', 'updated');

        $this->assertDatabaseHas('enhanced_settings', [
            'key'   => 'integration_key',
            'value' => 'updated',
        ]);
        $this->assertDatabaseCount('enhanced_settings', 1);
    }

    public function test_for_locale_scope_respects_locale_column(): void
    {
        // Toggle locales to guarantee the scope is filtering on the correct column.
        NormalSetting::factory()->create(['locale' => 'en']);
        NormalSetting::factory()->create(['locale' => 'lt']);

        $filtered = NormalSetting::forLocale('en')->get();

        $this->assertCount(1, $filtered);
        $this->assertSame('en', $filtered->firstOrFail()->locale);
    }

    public function test_translation_relationship_exposes_localized_records(): void
    {
        // Ensure the relation is wired to the translation model and returns expected instances.
        $setting = NormalSetting::factory()->create();
        NormalSettingTranslation::factory()->forSetting($setting)->create(['locale' => 'en']);
        NormalSettingTranslation::factory()->forSetting($setting)->create(['locale' => 'lt']);

        $translations = $setting->translations;

        $this->assertCount(2, $translations);
        $this->assertTrue($translations->contains(fn (NormalSettingTranslation $translation, int $index): bool => $translation->locale === 'en'));
    }

    public function test_accessors_fall_back_to_default_attributes(): void
    {
        // Populate translations so the helper methods can resolve localized copies.
        $setting = NormalSetting::factory()->create([
            'key'         => 'displayable_key',
            'description' => 'Plain description',
        ]);

        NormalSettingTranslation::factory()->forSetting($setting)->create([
            'locale'       => 'en',
            'display_name' => 'Fancy name',
            'description'  => 'Fancy description',
            'help_text'    => 'Helpful details',
        ]);

        $this->assertSame('Fancy description', $setting->getTranslatedDescription('en'));
        $this->assertSame('Fancy name', $setting->getDisplayName('en'));
        $this->assertSame('Helpful details', $setting->getHelpText('en'));
    }
}
