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

        $translation = SystemSettingTranslation::create([
            'system_setting_id' => $systemSetting->id,
            'locale' => 'en',
            'name' => 'Test Translation',
            'description' => 'Test Description',
            'help_text' => 'Test Help Text',
        ]);

        $this->assertDatabaseHas('system_setting_translations', [
            'system_setting_id' => $systemSetting->id,
            'locale' => 'en',
            'name' => 'Test Translation',
            'description' => 'Test Description',
            'help_text' => 'Test Help Text',
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
        ];

        $this->assertEquals($expectedFillable, $translation->getFillable());
    }

    public function test_can_replicate_translation(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $originalTranslation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'name' => 'Original Name',
        ]);

        $replicatedTranslation = $originalTranslation->replicate();
        $replicatedTranslation->name = 'Replicated Name';
        $replicatedTranslation->save();

        $this->assertDatabaseHas('system_setting_translations', [
            'system_setting_id' => $systemSetting->id,
            'name' => 'Replicated Name',
        ]);

        $this->assertDatabaseHas('system_setting_translations', [
            'system_setting_id' => $systemSetting->id,
            'name' => 'Original Name',
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
            'id' => $translation->id,
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
            'locale' => 'en',
        ]);

        SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'locale' => 'lt',
        ]);

        $englishTranslations = SystemSettingTranslation::where('locale', 'en')->get();
        $lithuanianTranslations = SystemSettingTranslation::where('locale', 'lt')->get();

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

        $translationsForSetting1 = SystemSettingTranslation::where('system_setting_id', $systemSetting1->id)->get();
        $translationsForSetting2 = SystemSettingTranslation::where('system_setting_id', $systemSetting2->id)->get();

        $this->assertCount(1, $translationsForSetting1);
        $this->assertCount(1, $translationsForSetting2);
    }

    public function test_can_get_translation_by_locale(): void
    {
        $systemSetting = SystemSetting::factory()->create();

        $englishTranslation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'locale' => 'en',
            'name' => 'English Name',
        ]);

        $lithuanianTranslation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'locale' => 'lt',
            'name' => 'Lithuanian Name',
        ]);

        $foundEnglish = SystemSettingTranslation::where('system_setting_id', $systemSetting->id)
            ->where('locale', 'en')
            ->first();

        $foundLithuanian = SystemSettingTranslation::where('system_setting_id', $systemSetting->id)
            ->where('locale', 'lt')
            ->first();

        $this->assertEquals($englishTranslation->id, $foundEnglish->id);
        $this->assertEquals($lithuanianTranslation->id, $foundLithuanian->id);
    }

    public function test_can_get_all_translations_for_system_setting(): void
    {
        $systemSetting = SystemSetting::factory()->create();

        SystemSettingTranslation::factory()->count(3)->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        $translations = SystemSettingTranslation::where('system_setting_id', $systemSetting->id)->get();

        $this->assertCount(3, $translations);
    }

    public function test_can_get_translation_count_by_locale(): void
    {
        $systemSetting = SystemSetting::factory()->create();

        SystemSettingTranslation::factory()->count(2)->create([
            'system_setting_id' => $systemSetting->id,
            'locale' => 'en',
        ]);

        SystemSettingTranslation::factory()->count(3)->create([
            'system_setting_id' => $systemSetting->id,
            'locale' => 'lt',
        ]);

        $englishCount = SystemSettingTranslation::where('locale', 'en')->count();
        $lithuanianCount = SystemSettingTranslation::where('locale', 'lt')->count();

        $this->assertEquals(2, $englishCount);
        $this->assertEquals(3, $lithuanianCount);
    }
}
