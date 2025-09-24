<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\NavigationGroup;
use App\Models\NormalSetting;
use App\Models\NormalSettingTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestCase;

final class NormalSettingTranslationResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_normal_setting_translations(): void
    {
        $this->actingAs($this->adminUser);

        $setting = NormalSetting::factory()->create([
            'key' => 'test_setting',
            'group' => 'general',
        ]);

        NormalSettingTranslation::factory()->create([
            'enhanced_setting_id' => $setting->id,
            'locale' => 'en',
            'display_name' => 'Test Setting',
        ]);

        Livewire::test(\App\Filament\Resources\NormalSettingTranslationResource\Pages\ListNormalSettingTranslations::class)
            ->assertCanSeeTableRecords(NormalSettingTranslation::all());
    }

    public function test_can_create_normal_setting_translation(): void
    {
        $this->actingAs($this->adminUser);

        $setting = NormalSetting::factory()->create([
            'key' => 'test_setting',
            'group' => 'general',
        ]);

        Livewire::test(\App\Filament\Resources\NormalSettingTranslationResource\Pages\CreateNormalSettingTranslation::class)
            ->fillForm([
                'enhanced_setting_id' => $setting->id,
                'locale' => 'en',
                'display_name' => 'Test Setting',
                'description' => 'Test description',
                'help_text' => 'Test help text',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('enhanced_settings_translations', [
            'enhanced_setting_id' => $setting->id,
            'locale' => 'en',
            'display_name' => 'Test Setting',
        ]);
    }

    public function test_can_edit_normal_setting_translation(): void
    {
        $this->actingAs($this->adminUser);

        $setting = NormalSetting::factory()->create();
        $translation = NormalSettingTranslation::factory()->create([
            'enhanced_setting_id' => $setting->id,
            'locale' => 'en',
            'display_name' => 'Original Name',
        ]);

        Livewire::test(\App\Filament\Resources\NormalSettingTranslationResource\Pages\EditNormalSettingTranslation::class, [
            'record' => $translation->getRouteKey(),
        ])
            ->fillForm([
                'display_name' => 'Updated Name',
                'description' => 'Updated description',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('enhanced_settings_translations', [
            'id' => $translation->id,
            'display_name' => 'Updated Name',
        ]);
    }

    public function test_can_view_normal_setting_translation(): void
    {
        $this->actingAs($this->adminUser);

        $setting = NormalSetting::factory()->create();
        $translation = NormalSettingTranslation::factory()->create([
            'enhanced_setting_id' => $setting->id,
            'locale' => 'en',
            'display_name' => 'Test Setting',
        ]);

        Livewire::test(\App\Filament\Resources\NormalSettingTranslationResource\Pages\ViewNormalSettingTranslation::class, [
            'record' => $translation->getRouteKey(),
        ])
            ->assertCanSeeFormData([
                'display_name' => 'Test Setting',
            ]);
    }

    public function test_can_delete_normal_setting_translation(): void
    {
        $this->actingAs($this->adminUser);

        $setting = NormalSetting::factory()->create();
        $translation = NormalSettingTranslation::factory()->create([
            'enhanced_setting_id' => $setting->id,
        ]);

        Livewire::test(\App\Filament\Resources\NormalSettingTranslationResource\Pages\ListNormalSettingTranslations::class)
            ->callTableAction('delete', $translation);

        $this->assertDatabaseMissing('enhanced_settings_translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_can_filter_by_enhanced_setting(): void
    {
        $this->actingAs($this->adminUser);

        $setting1 = NormalSetting::factory()->create(['key' => 'setting1']);
        $setting2 = NormalSetting::factory()->create(['key' => 'setting2']);

        $translation1 = NormalSettingTranslation::factory()->create([
            'enhanced_setting_id' => $setting1->id,
        ]);
        $translation2 = NormalSettingTranslation::factory()->create([
            'enhanced_setting_id' => $setting2->id,
        ]);

        Livewire::test(\App\Filament\Resources\NormalSettingTranslationResource\Pages\ListNormalSettingTranslations::class)
            ->filterTable('enhanced_setting_id', $setting1->id)
            ->assertCanSeeTableRecords([$translation1])
            ->assertCanNotSeeTableRecords([$translation2]);
    }

    public function test_can_filter_by_locale(): void
    {
        $this->actingAs($this->adminUser);

        $setting = NormalSetting::factory()->create();

        $enTranslation = NormalSettingTranslation::factory()->create([
            'enhanced_setting_id' => $setting->id,
            'locale' => 'en',
        ]);
        $ltTranslation = NormalSettingTranslation::factory()->create([
            'enhanced_setting_id' => $setting->id,
            'locale' => 'lt',
        ]);

        Livewire::test(\App\Filament\Resources\NormalSettingTranslationResource\Pages\ListNormalSettingTranslations::class)
            ->filterTable('locale', 'en')
            ->assertCanSeeTableRecords([$enTranslation])
            ->assertCanNotSeeTableRecords([$ltTranslation]);
    }

    public function test_validation_requires_enhanced_setting(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NormalSettingTranslationResource\Pages\CreateNormalSettingTranslation::class)
            ->fillForm([
                'locale' => 'en',
                'display_name' => 'Test Setting',
            ])
            ->call('create')
            ->assertHasFormErrors(['enhanced_setting_id']);
    }

    public function test_validation_requires_locale(): void
    {
        $this->actingAs($this->adminUser);

        $setting = NormalSetting::factory()->create();

        Livewire::test(\App\Filament\Resources\NormalSettingTranslationResource\Pages\CreateNormalSettingTranslation::class)
            ->fillForm([
                'enhanced_setting_id' => $setting->id,
                'display_name' => 'Test Setting',
            ])
            ->call('create')
            ->assertHasFormErrors(['locale']);
    }

    public function test_validation_requires_display_name(): void
    {
        $this->actingAs($this->adminUser);

        $setting = NormalSetting::factory()->create();

        Livewire::test(\App\Filament\Resources\NormalSettingTranslationResource\Pages\CreateNormalSettingTranslation::class)
            ->fillForm([
                'enhanced_setting_id' => $setting->id,
                'locale' => 'en',
            ])
            ->call('create')
            ->assertHasFormErrors(['display_name']);
    }

    public function test_navigation_group_is_system(): void
    {
        $this->assertEquals(NavigationGroup::System, \App\Filament\Resources\NormalSettingTranslationResource::getNavigationGroup());
    }

    public function test_has_correct_navigation_sort(): void
    {
        $this->assertEquals(16, \App\Filament\Resources\NormalSettingTranslationResource::getNavigationSort());
    }

    public function test_has_correct_record_title_attribute(): void
    {
        $this->assertEquals('display_name', \App\Filament\Resources\NormalSettingTranslationResource::getRecordTitleAttribute());
    }
}
