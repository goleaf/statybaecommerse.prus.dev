<?php

declare(strict_types=1);

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    $admin = User::factory()->create([
        'email'    => 'info@egisstatyba.lt',
        'is_admin' => true,
    ]);

    $this->actingAs($admin);
});

it('lists services in the admin panel', function (): void {
    $services = Service::factory()->count(3)->create();

    livewire(ListServices::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords($services);
});

it('creates a service via the admin form', function (): void {
    livewire(CreateService::class)
        ->fillForm([
            'name'        => 'Assembly Service',
            'description' => 'On-site assembly for construction components.',
            'price'       => 120.00,
            'is_active'   => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('services', [
        'name'      => 'Assembly Service',
        'price'     => 120.00,
        'is_active' => true,
    ]);
});

it('edits a service via the admin form', function (): void {
    $service = Service::factory()->create([
        'name'      => 'Initial Service',
        'price'     => 85.50,
        'is_active' => true,
    ]);

    livewire(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm([
            'name'      => 'Updated Service',
            'price'     => 99.00,
            'is_active' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('services', [
        'id'        => $service->id,
        'name'      => 'Updated Service',
        'price'     => 99.00,
        'is_active' => false,
    ]);
});

it('uses translated resource labels in lithuanian locale', function (): void {
    $previousLocale = app()->getLocale();

    app()->setLocale('lt');

    try {
        expect(ServiceResource::getModelLabel())->toBe(__('translations.service'))
            ->and(ServiceResource::getPluralModelLabel())->toBe(__('translations.services'))
            ->and(ServiceResource::getNavigationLabel())->toBe(__('translations.services'))
            ->and(ServiceResource::getModelLabel())->toBe('Paslauga')
            ->and(ServiceResource::getPluralModelLabel())->toBe('Paslaugos');
    } finally {
        app()->setLocale($previousLocale);
    }
});
