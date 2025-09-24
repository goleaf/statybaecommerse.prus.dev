<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\SystemSettingTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingTranslationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_system_setting_translations(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translations = SystemSettingTranslation::factory()->count(3)->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\ListSystemSettingTranslations::class)
            ->assertCanSeeTableRecords($translations);
    }

    public function test_can_create_system_setting_translation(): void
    {
        $systemSetting = SystemSetting::factory()->create();

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\CreateSystemSettingTranslation::class)
            ->fillForm([
                'system_setting_id' => $systemSetting->id,
                'locale' => 'en',
                'name' => 'Test Translation',
                'description' => 'Test Description',
                'help_text' => 'Test Help Text',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_translations', [
            'system_setting_id' => $systemSetting->id,
            'locale' => 'en',
            'name' => 'Test Translation',
            'description' => 'Test Description',
            'help_text' => 'Test Help Text',
        ]);
    }

    public function test_can_edit_system_setting_translation(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\EditSystemSettingTranslation::class, [
            'record' => $translation->id,
        ])
            ->fillForm([
                'name' => 'Updated Translation',
                'description' => 'Updated Description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_translations', [
            'id' => $translation->id,
            'name' => 'Updated Translation',
            'description' => 'Updated Description',
        ]);
    }

    public function test_can_view_system_setting_translation(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\ViewSystemSettingTranslation::class, [
            'record' => $translation->id,
        ])
            ->assertCanSeeText($translation->name)
            ->assertCanSeeText($translation->description);
    }

    public function test_can_delete_system_setting_translation(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\ListSystemSettingTranslations::class)
            ->callTableAction('delete', $translation);

        $this->assertDatabaseMissing('system_setting_translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_can_duplicate_system_setting_translation(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\ListSystemSettingTranslations::class)
            ->callTableAction('duplicate', $translation);

        $this->assertDatabaseHas('system_setting_translations', [
            'system_setting_id' => $systemSetting->id,
            'locale' => $translation->locale,
            'name' => $translation->name.' (Copy)',
        ]);
    }

    public function test_can_filter_by_locale(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $englishTranslation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'locale' => 'en',
        ]);
        $lithuanianTranslation = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'locale' => 'lt',
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\ListSystemSettingTranslations::class)
            ->filterTable('locale', 'en')
            ->assertCanSeeTableRecords([$englishTranslation])
            ->assertCanNotSeeTableRecords([$lithuanianTranslation]);
    }

    public function test_can_filter_by_system_setting(): void
    {
        $systemSetting1 = SystemSetting::factory()->create();
        $systemSetting2 = SystemSetting::factory()->create();

        $translation1 = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting1->id,
        ]);
        $translation2 = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting2->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\ListSystemSettingTranslations::class)
            ->filterTable('system_setting_id', $systemSetting1->id)
            ->assertCanSeeTableRecords([$translation1])
            ->assertCanNotSeeTableRecords([$translation2]);
    }

    public function test_can_bulk_activate_translations(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translations = SystemSettingTranslation::factory()->count(3)->create([
            'system_setting_id' => $systemSetting->id,
            'is_active' => false,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\ListSystemSettingTranslations::class)
            ->callTableBulkAction('activate', $translations);

        foreach ($translations as $translation) {
            $this->assertDatabaseHas('system_setting_translations', [
                'id' => $translation->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_translations(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translations = SystemSettingTranslation::factory()->count(3)->create([
            'system_setting_id' => $systemSetting->id,
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\ListSystemSettingTranslations::class)
            ->callTableBulkAction('deactivate', $translations);

        foreach ($translations as $translation) {
            $this->assertDatabaseHas('system_setting_translations', [
                'id' => $translation->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_can_search_translations(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translation1 = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'name' => 'Unique Name',
        ]);
        $translation2 = SystemSettingTranslation::factory()->create([
            'system_setting_id' => $systemSetting->id,
            'name' => 'Different Name',
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\ListSystemSettingTranslations::class)
            ->searchTable('Unique')
            ->assertCanSeeTableRecords([$translation1])
            ->assertCanNotSeeTableRecords([$translation2]);
    }

    public function test_form_validation_works(): void
    {
        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\CreateSystemSettingTranslation::class)
            ->fillForm([
                'system_setting_id' => null,
                'locale' => '',
                'name' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['system_setting_id', 'locale', 'name']);
    }

    public function test_can_export_translations(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translations = SystemSettingTranslation::factory()->count(3)->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\ListSystemSettingTranslations::class)
            ->callTableBulkAction('export_translations', $translations)
            ->assertNotified();
    }

    public function test_can_import_translations(): void
    {
        $systemSetting = SystemSetting::factory()->create();
        $translations = SystemSettingTranslation::factory()->count(3)->create([
            'system_setting_id' => $systemSetting->id,
        ]);

        Livewire::test(\App\Filament\Resources\SystemSettingTranslationResource\Pages\ListSystemSettingTranslations::class)
            ->callTableBulkAction('import_translations', $translations)
            ->assertNotified();
    }
}
