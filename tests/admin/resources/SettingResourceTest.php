<?php

declare(strict_types=1);

use App\Filament\Resources\Settings\Pages\CreateSetting;
use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('lists existing settings in the table', function (): void {
    $settings = Setting::factory()->count(3)->create();

    Livewire::actingAs($this->admin)
        ->test(ListSettings::class)
        ->assertCanSeeTableRecords($settings);
});

it('creates a new setting record', function (): void {
    $payload = [
        'key'          => 'site_name',
        'value'        => 'My Storefront',
        'type'         => 'string',
        'description'  => 'Primary storefront name',
        'is_public'    => true,
        'display_name' => 'Store Name',
        'group'        => 'general',
        'is_required'  => false,
        'is_encrypted' => false,
    ];

    Livewire::actingAs($this->admin)
        ->test(CreateSetting::class)
        ->fillForm($payload)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('settings', [
        'key'          => 'site_name',
        'type'         => 'string',
        'group'        => 'general',
        'display_name' => 'Store Name',
        'is_public'    => true,
    ]);
});

it('validates required fields when creating a setting', function (): void {
    Livewire::actingAs($this->admin)
        ->test(CreateSetting::class)
        ->fillForm([
            'key'          => '',
            'type'         => '',
            'is_public'    => null,
            'is_required'  => null,
            'is_encrypted' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'key'          => 'required',
            'type'         => 'required',
            'is_public'    => 'required',
            'is_required'  => 'required',
            'is_encrypted' => 'required',
        ]);
});

it('updates an existing setting record', function (): void {
    $setting = Setting::factory()->create([
        'key'          => 'site_tagline',
        'value'        => 'Old tagline',
        'type'         => 'string',
        'description'  => 'Old description',
        'display_name' => 'Tagline',
        'group'        => 'branding',
        'is_public'    => false,
        'is_required'  => true,
        'is_encrypted' => false,
    ]);

    Livewire::actingAs($this->admin)
        ->test(EditSetting::class, ['record' => $setting->getKey()])
        ->fillForm([
            'value'        => 'New tagline',
            'description'  => 'Updated description',
            'display_name' => 'Brand Tagline',
            'group'        => 'marketing',
            'is_public'    => true,
            'is_required'  => false,
            'is_encrypted' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('settings', [
        'id'           => $setting->id,
        'value'        => 'New tagline',
        'description'  => 'Updated description',
        'display_name' => 'Brand Tagline',
        'group'        => 'marketing',
        'is_public'    => true,
        'is_required'  => false,
        'is_encrypted' => false,
    ]);
});

it('can delete a setting from the edit page', function (): void {
    $setting = Setting::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(EditSetting::class, ['record' => $setting->getKey()])
        ->callAction('delete')
        ->assertOk();

    $this->assertDatabaseMissing('settings', [
        'id' => $setting->id,
    ]);
});
