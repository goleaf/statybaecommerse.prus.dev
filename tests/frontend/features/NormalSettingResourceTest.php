<?php

declare(strict_types=1);

use App\Filament\Resources\NormalSettingResource;
use App\Models\NormalSetting;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('renders normal settings index', function () {
    actingAs($this->admin)
        ->get(NormalSettingResource::getUrl('index'))
        ->assertSuccessful();
});

it('renders normal settings create page', function () {
    actingAs($this->admin)
        ->get(NormalSettingResource::getUrl('create'))
        ->assertSuccessful();
});

it('creates a normal setting', function () {
    $payload = [
        'group' => 'general',
        'key' => 'homepage_title',
        'type' => 'text',
        'value' => 'Welcome',
        'description' => 'Homepage H1 title',
        'is_public' => true,
        'is_encrypted' => false,
        'validation_rules' => [],
        'sort_order' => 0,
    ];

    actingAs($this->admin)
        ->post(NormalSettingResource::getUrl('create'), $payload)
        ->assertRedirect();

    expect(NormalSetting::query()->where('key', 'homepage_title')->exists())->toBeTrue();
});

it('views, edits and deletes a normal setting', function () {
    $record = NormalSetting::factory()->create([
        'group' => 'general',
        'key' => 'temp_key',
        'type' => 'text',
        'value' => 'old',
    ]);

    actingAs($this->admin)
        ->get(NormalSettingResource::getUrl('view', ['record' => $record]))
        ->assertSuccessful();

    $update = [
        'group' => 'general',
        'key' => 'updated_key',
        'type' => 'text',
        'value' => 'new',
        'description' => $record->description,
        'is_public' => $record->is_public,
        'is_encrypted' => $record->is_encrypted,
        'validation_rules' => $record->validation_rules,
        'sort_order' => $record->sort_order,
    ];

    actingAs($this->admin)
        ->put(NormalSettingResource::getUrl('edit', ['record' => $record]), $update)
        ->assertRedirect();

    $record->refresh();
    expect($record->key)->toBe('updated_key');
    expect($record->value)->toBe('new');

    actingAs($this->admin)
        ->delete(NormalSettingResource::getUrl('edit', ['record' => $record]))
        ->assertRedirect();

    expect(NormalSetting::query()->whereKey($record->getKey())->exists())->toBeFalse();
});
