<?php

declare(strict_types=1);

use App\Filament\Resources\EnhancedSettingResource;
use App\Models\NormalSetting as EnhancedSetting;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('can render enhanced settings index page', function () {
    actingAs($this->admin)
        ->get(EnhancedSettingResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render enhanced settings create page', function () {
    actingAs($this->admin)
        ->get(EnhancedSettingResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create enhanced setting', function () {
    $payload = [
        'group' => 'general',
        'key' => 'site_name',
        'type' => 'text',
        'value' => 'My Shop',
        'description' => 'Site display name',
        'is_public' => true,
        'is_encrypted' => false,
        'validation_rules' => [],
        'sort_order' => 0,
    ];

    actingAs($this->admin)
        ->post(EnhancedSettingResource::getUrl('create'), $payload)
        ->assertRedirect();

    expect(EnhancedSetting::query()->where('key', 'site_name')->exists())->toBeTrue();
});

it('can render enhanced setting view and edit pages', function () {
    $record = EnhancedSetting::factory()->create();

    actingAs($this->admin)
        ->get(EnhancedSettingResource::getUrl('view', ['record' => $record]))
        ->assertSuccessful();

    actingAs($this->admin)
        ->get(EnhancedSettingResource::getUrl('edit', ['record' => $record]))
        ->assertSuccessful();
});

it('can update enhanced setting', function () {
    $record = EnhancedSetting::factory()->create([
        'key' => 'old_key',
        'value' => 'old',
    ]);

    $update = [
        'group' => $record->group,
        'key' => 'new_key',
        'type' => $record->type,
        'value' => 'new',
        'description' => $record->description,
        'is_public' => $record->is_public,
        'is_encrypted' => $record->is_encrypted,
        'validation_rules' => $record->validation_rules,
        'sort_order' => $record->sort_order,
    ];

    actingAs($this->admin)
        ->put(EnhancedSettingResource::getUrl('edit', ['record' => $record]), $update)
        ->assertRedirect();

    $record->refresh();
    expect($record->key)->toBe('new_key');
    expect($record->value)->toBe('new');
});

it('can delete enhanced setting', function () {
    $record = EnhancedSetting::factory()->create();

    actingAs($this->admin)
        ->delete(EnhancedSettingResource::getUrl('edit', ['record' => $record]))
        ->assertRedirect();

    expect(EnhancedSetting::query()->whereKey($record->getKey())->exists())->toBeFalse();
});
