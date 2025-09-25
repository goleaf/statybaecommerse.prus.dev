<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\SystemSettingResource as SystemSettingsResource;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingsResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_can_render_settings_index_page(): void
    {
        $this
            ->get(SystemSettingsResource::getUrl('index'))
            ->assertOk();
    }

    public function test_can_list_settings(): void
    {
        $settings = Setting::factory()->count(3)->create();

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->assertCanSeeTableRecords($settings);
    }

    public function test_can_render_settings_create_page(): void
    {
        $this
            ->get(SystemSettingsResource::getUrl('create'))
            ->assertOk();
    }

    public function test_can_create_setting(): void
    {
        $newData = Setting::factory()->make();

        Livewire::test(SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->fillForm([
                'key' => $newData->key,
                'display_name' => $newData->display_name,
                'type' => $newData->type,
                'value' => $newData->value,
                'description' => $newData->description,
                'group' => $newData->group,
                'is_public' => $newData->is_public,
                'is_required' => $newData->is_required,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('settings', [
            'key' => $newData->key,
            'display_name' => $newData->display_name,
            'type' => $newData->type,
        ]);
    }

    public function test_can_render_settings_edit_page(): void
    {
        $setting = Setting::factory()->create();

        $this
            ->get(SystemSettingsResource::getUrl('edit', ['record' => $setting]))
            ->assertOk();
    }

    public function test_can_edit_setting(): void
    {
        $setting = Setting::factory()->create();
        $newData = Setting::factory()->make();

        Livewire::test(SystemSettingsResource\Pages\EditSystemSetting::class, ['record' => $setting->getRouteKey()])
            ->fillForm([
                'key' => $newData->key,
                'display_name' => $newData->display_name,
                'value' => $newData->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($setting->refresh())
            ->key
            ->toBe($newData->key)
            ->display_name
            ->toBe($newData->display_name)
            ->value
            ->toBe($newData->value);
    }

    public function test_can_delete_setting(): void
    {
        $setting = Setting::factory()->create();

        Livewire::test(SystemSettingsResource\Pages\EditSystemSetting::class, ['record' => $setting->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($setting);
    }

    public function test_can_validate_setting_key_is_required(): void
    {
        Livewire::test(SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->fillForm([
                'key' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['key' => 'required']);
    }

    public function test_can_validate_setting_key_is_unique(): void
    {
        $setting = Setting::factory()->create();

        Livewire::test(SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->fillForm([
                'key' => $setting->key,
            ])
            ->call('create')
            ->assertHasFormErrors(['key' => 'unique']);
    }

    public function test_can_filter_settings_by_type(): void
    {
        $stringSetting = Setting::factory()->create(['type' => 'string']);
        $booleanSetting = Setting::factory()->create(['type' => 'boolean']);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->filterTable('type', 'string')
            ->assertCanSeeTableRecords([$stringSetting])
            ->assertCanNotSeeTableRecords([$booleanSetting]);
    }

    public function test_can_filter_settings_by_group(): void
    {
        $generalSetting = Setting::factory()->create(['group' => 'general']);
        $emailSetting = Setting::factory()->create(['group' => 'email']);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->filterTable('group', 'general')
            ->assertCanSeeTableRecords([$generalSetting])
            ->assertCanNotSeeTableRecords([$emailSetting]);
    }

    public function test_can_search_settings_by_key(): void
    {
        $settingA = Setting::factory()->create(['key' => 'app_name']);
        $settingB = Setting::factory()->create(['key' => 'email_from']);

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->searchTable('app_name')
            ->assertCanSeeTableRecords([$settingA])
            ->assertCanNotSeeTableRecords([$settingB]);
    }

    public function test_can_bulk_delete_settings(): void
    {
        $settings = Setting::factory()->count(3)->create();

        Livewire::test(SystemSettingsResource\Pages\ListSystemSettings::class)
            ->callTableBulkAction('delete', $settings);

        foreach ($settings as $setting) {
            $this->assertModelMissing($setting);
        }
    }
}
