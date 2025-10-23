<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SettingResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_list_settings(): void
    {
        $settings = Setting::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\Settings\Pages\ListSettings::class)
            ->assertCanSeeTableRecords($settings);
    }

    public function test_can_create_setting(): void
    {
        $formData = [
            'key'          => 'site_name',
            'value'        => 'Statyba E-Commerce',
            'type'         => 'string',
            'description'  => 'Display name for the storefront.',
            'is_public'    => true,
            'display_name' => 'Site Name',
            'group'        => 'general',
            'is_required'  => false,
            'is_encrypted' => false,
        ];

        Livewire::test(\App\Filament\Resources\Settings\Pages\CreateSetting::class)
            ->fillForm($formData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('settings', [
            'key'       => 'site_name',
            'value'     => 'Statyba E-Commerce',
            'type'      => 'string',
            'is_public' => true,
            'group'     => 'general',
        ]);
    }

    public function test_can_edit_setting(): void
    {
        $setting = Setting::factory()->create([
            'value'       => 'Old Value',
            'description' => 'Original description',
            'is_public'   => false,
        ]);

        Livewire::test(\App\Filament\Resources\Settings\Pages\EditSetting::class, [
            'record' => $setting->getRouteKey(),
        ])
            ->fillForm([
                'value'        => 'Updated Value',
                'description'  => 'Updated description',
                'is_public'    => true,
                'is_required'  => true,
                'is_encrypted' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('settings', [
            'id'           => $setting->id,
            'value'        => 'Updated Value',
            'description'  => 'Updated description',
            'is_public'    => true,
            'is_required'  => true,
            'is_encrypted' => true,
        ]);
    }

    public function test_can_bulk_delete_settings(): void
    {
        $settings = Setting::factory()->count(2)->create();

        Livewire::test(\App\Filament\Resources\Settings\Pages\ListSettings::class)
            ->callTableBulkAction('delete', $settings)
            ->assertHasNoTableBulkActionErrors();

        foreach ($settings as $setting) {
            $this->assertDatabaseMissing('settings', [
                'id' => $setting->id,
            ]);
        }
    }
}
