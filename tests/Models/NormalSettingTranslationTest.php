<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\NormalSetting;
use App\Models\NormalSettingTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NormalSettingTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes_protect_mass_assignment(): void
    {
        // Instantiate the model so we can assert the underlying configuration without hitting the database.
        $model = new NormalSettingTranslation;

        // Confirm the fillable array matches the attributes that should be mass assignable.
        self::assertSame([
            'enhanced_setting_id',
            'locale',
            'description',
            'display_name',
            'help_text',
        ], $model->getFillable());
    }

    public function test_enhanced_setting_relationship_links_parent_setting(): void
    {
        // Create a parent setting so the translation can reference an actual record.
        $setting = NormalSetting::factory()->create();

        // Persist a translation that belongs to the newly created parent setting.
        $translation = NormalSettingTranslation::factory()->create([
            'enhanced_setting_id' => $setting->getKey(),
        ]);

        // Refresh the relation to ensure the belongsTo association returns the correct instance.
        self::assertTrue($setting->is($translation->enhancedSetting));
    }

    public function test_scope_ordered_by_name_sorts_by_display_name(): void
    {
        // Create translations with deliberately unordered display names for the scope to sort.
        $first = NormalSettingTranslation::factory()->create(['display_name' => 'Zebra Setting']);
        $second = NormalSettingTranslation::factory()->create(['display_name' => 'Alpha Setting']);

        // Invoke the scope and collect the resulting order to verify alphabetical sorting.
        $orderedIds = NormalSettingTranslation::query()
            ->orderedByName()
            ->pluck('id')
            ->all();

        // The record with the alphabetically first name should appear before the later one.
        self::assertSame([$second->getKey(), $first->getKey()], $orderedIds);
    }
}
