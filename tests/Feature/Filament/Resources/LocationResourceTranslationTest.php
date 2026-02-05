<?php

declare(strict_types=1);

use App\Filament\Resources\LocationResource\Pages\CreateLocation;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->resolveAdminPanel();

    config([
        'app.locale'                             => 'lt',
        'app.fallback_locale'                    => 'en',
        'filament-language-tabs.default_locales' => ['lt', 'en'],
        'app.locales'                            => [
            'lt' => ['native' => 'Lietuviu'],
            'en' => ['native' => 'English'],
        ],
    ]);

    app()->setLocale('lt');

    $this->admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('stores translated fields and syncs the default locale', function (): void {
    Livewire::test(CreateLocation::class)
        ->fillForm([
            'code' => 'VIL-001',
            'type' => 'warehouse',
            'name' => [
                'lt' => 'Vilniaus sandelis',
                'en' => 'Vilnius Warehouse',
            ],
            'slug' => [
                'lt' => 'vilniaus-sandelis',
                'en' => 'vilnius-warehouse',
            ],
            'description' => [
                'lt' => 'Sandelio aprasymas',
                'en' => 'Warehouse description',
            ],
            'is_enabled' => true,
            'is_default' => false,
            'sort_order' => 0,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $location = Location::query()->where('code', 'VIL-001')->first();

    expect($location)->not->toBeNull();

    $this->assertDatabaseHas('locations', [
        'id'          => $location->id,
        'name'        => 'Vilniaus sandelis',
        'slug'        => 'vilniaus-sandelis',
        'description' => 'Sandelio aprasymas',
    ]);

    $this->assertDatabaseHas('location_translations', [
        'location_id' => $location->id,
        'locale'      => 'lt',
        'name'        => 'Vilniaus sandelis',
        'slug'        => 'vilniaus-sandelis',
        'description' => 'Sandelio aprasymas',
    ]);

    $this->assertDatabaseHas('location_translations', [
        'location_id' => $location->id,
        'locale'      => 'en',
        'name'        => 'Vilnius Warehouse',
        'slug'        => 'vilnius-warehouse',
        'description' => 'Warehouse description',
    ]);
});
