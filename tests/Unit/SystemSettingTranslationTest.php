<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SystemSetting;
use App\Models\SystemSettingTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SystemSettingTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_system_setting_translation(): void
    {
        $systemSetting = SystemSetting::factory()->create();

        // Persist a translation using mass assignment to confirm the fillable
        // configuration allows end-to-end creation in the database layer.
        SystemSettingTranslation::create([
            'system_setting_id' => $systemSetting->id,
            'locale'            => 'en',
            'name'              => 'Test Translation',
            'description'       => 'Test Description',
            'help_text'         => 'Test Help Text',
        ]);

        $this->assertDatabaseHas('system_setting_translations', [
            'system_setting_id' => $systemSetting->id,
            'locale'            => 'en',
            'name'              => 'Test Translation',
            'description'       => 'Test Description',
            'help_text'         => 'Test Help Text',
        ]);
    }

    public function test_belongs_to_system_setting(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        $this->assertInstanceOf(SystemSetting::class, $translation->systemSetting);
        $this->assertEquals($systemSetting->id, $translation->systemSetting->id);
    }

    public function test_fillable_attributes(): void
    {
        $translation = new SystemSettingTranslation;

        $expectedFillable = [
            'system_setting_id',
            'locale',
            'name',
            'description',
            'help_text',
            'rich_description',
            'attachments',
            'meta',
            'metadata',
            'tags',
            'is_active',
            'is_public',
            'sort_order',
        ];

        $this->assertEquals($expectedFillable, $translation->getFillable());
    }

    public function test_can_replicate_translation(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $originalTranslation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'name'              => 'Original Name',
        ]);

        $replicatedTranslation = $originalTranslation->replicate();
        $replicatedTranslation->name = 'Replicated Name';
        $replicatedTranslation->save();

        $this->assertDatabaseHas('system_setting_translations', [
            'system_setting_id' => $systemSetting->id,
            'name'              => 'Replicated Name',
        ]);

        $this->assertDatabaseHas('system_setting_translations', [
            'system_setting_id' => $systemSetting->id,
            'name'              => 'Original Name',
        ]);
    }

    public function test_can_soft_delete_translation(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        $translation->delete();

        $this->assertSoftDeleted('system_setting_translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_can_restore_translation(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        $translation->delete();
        $translation->restore();

        $this->assertDatabaseHas('system_setting_translations', [
            'id'         => $translation->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_force_delete_translation(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        $translation->forceDelete();

        $this->assertDatabaseMissing('system_setting_translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_can_scope_by_locale(): void
    {
        $systemSetting = SystemSetting::factory()->create();

        SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'locale'            => 'en',
        ]);

        SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'locale'            => 'lt',
        ]);

        // Exercise the dedicated scope so we confirm locale filtering respects
        // the normalisation logic declared on the model itself.
        $englishTranslations = SystemSettingTranslation::query()->forLocale('en')->get();
        $lithuanianTranslations = SystemSettingTranslation::query()->forLocale('lt')->get();

        $this->assertCount(1, $englishTranslations);
        $this->assertCount(1, $lithuanianTranslations);
    }

    public function test_can_scope_by_system_setting(): void
    {
        $systemSetting1 = SystemSetting::factory()->create();
        $systemSetting2 = SystemSetting::factory()->create();

        SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting1->id,
        ]);

        SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting2->id,
        ]);

        // Using the scope ensures the relation helper can safely accept either
        // raw identifiers or model instances without duplicating logic here.
        $translationsForSetting1 = SystemSettingTranslation::query()->forSystemSetting($systemSetting1)->get();
        $translationsForSetting2 = SystemSettingTranslation::query()->forSystemSetting($systemSetting2->id)->get();

        $this->assertCount(1, $translationsForSetting1);
        $this->assertCount(1, $translationsForSetting2);
    }

    public function test_can_get_translation_by_locale(): void
    {
        $systemSetting = SystemSetting::factory()->create();

        $englishTranslation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'locale'            => 'en',
            'name'              => 'English Name',
        ]);

        $lithuanianTranslation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'locale'            => 'lt',
            'name'              => 'Lithuanian Name',
        ]);

        // The helper combines the two reusable scopes to find a matching record.
        $foundEnglish = SystemSettingTranslation::findForLocale($systemSetting, 'en');
        $foundLithuanian = SystemSettingTranslation::findForLocale($systemSetting->id, 'lt');

        $this->assertNotNull($foundEnglish);
        $this->assertNotNull($foundLithuanian);

        $this->assertEquals($englishTranslation->id, $foundEnglish->id);
        $this->assertEquals($lithuanianTranslation->id, $foundLithuanian->id);
    }

    public function test_can_get_all_translations_for_system_setting(): void
    {
        $systemSetting = SystemSetting::factory()->create();

        SystemSettingTranslation::factory()->count(3)->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        // The convenience method orders records for deterministic admin listings.
        $translations = SystemSettingTranslation::allForSystemSetting($systemSetting);

        $this->assertCount(3, $translations);
    }

    public function test_can_get_translation_count_by_locale(): void
    {
        $systemSetting = SystemSetting::factory()->create();

        SystemSettingTranslation::factory()->count(2)->create([
            'system_setting_id' => $systemSetting->id,
            'locale'            => 'en',
        ]);

        SystemSettingTranslation::factory()->count(3)->create([
            'system_setting_id' => $systemSetting->id,
            'locale'            => 'lt',
        ]);

        // Counting through the helper ensures the locale normalisation path is covered.
        $englishCount = SystemSettingTranslation::countForLocale('en');
        $lithuanianCount = SystemSettingTranslation::countForLocale('lt');

        $this->assertEquals(2, $englishCount);
        $this->assertEquals(3, $lithuanianCount);
    }

    public function test_locale_is_normalized_on_save(): void
    {
        $systemSetting = SystemSetting::factory()->create();

        $translation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'locale'            => 'EN',
        ]);

        $this->assertSame('en', $translation->locale);
        $this->assertDatabaseHas('system_setting_translations', [
            'id'     => $translation->id,
            'locale' => 'en',
        ]);
    }
}
