<?php

declare(strict_types=1);

use App\Models\Region;
use App\Models\Country;
use App\Models\Zone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Filament\Pages\Actions\CreateAction;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->country = Country::factory()->create(['is_active' => true]);
    $this->zone = Zone::factory()->create(['is_active' => true]);
});

it('can list regions', function () {
    Region::factory()->count(5)->create();

    $this->actingAs($this->admin)
        ->get(route('filament.admin.resources.regions.index'))
        ->assertOk()
        ->assertSee('Regions');
});

it('can create a region', function () {
    $this->actingAs($this->admin);

    Livewire::test('filament.resources.regions.create')
        ->fillForm([
            'name' => 'Test Region',
            'country_id' => $this->country->id,
            'zone_id' => $this->zone->id,
            'level' => 1,
            'is_enabled' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('regions', [
        'name' => 'Test Region',
        'country_id' => $this->country->id,
        'zone_id' => $this->zone->id,
        'level' => 1,
        'is_enabled' => true,
    ]);
});

it('can view a region', function () {
    $region = Region::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('filament.admin.resources.regions.view', $region))
        ->assertOk()
        ->assertSee($region->name);
});

it('can edit a region', function () {
    $region = Region::factory()->create(['name' => 'Original Name']);

    $this->actingAs($this->admin);

    Livewire::test('filament.resources.regions.edit', ['record' => $region->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('regions', [
        'id' => $region->id,
        'name' => 'Updated Name',
    ]);
});

it('can delete a region', function () {
    $region = Region::factory()->create();

    $this->actingAs($this->admin);

    Livewire::test('filament.resources.regions.list')
        ->callTableAction('delete', $region)
        ->assertOk();

    $this->assertDatabaseMissing('regions', [
        'id' => $region->id,
    ]);
});

it('can filter regions by country', function () {
    $region1 = Region::factory()->create(['country_id' => $this->country->id]);
    $region2 = Region::factory()->create();

    $this->actingAs($this->admin);

    Livewire::test('filament.resources.regions.list')
        ->filterTable('country_id', $this->country->id)
        ->assertCanSeeTableRecords([$region1])
        ->assertCanNotSeeTableRecords([$region2]);
});

it('can filter regions by zone', function () {
    $region1 = Region::factory()->create(['zone_id' => $this->zone->id]);
    $region2 = Region::factory()->create();

    $this->actingAs($this->admin);

    Livewire::test('filament.resources.regions.list')
        ->filterTable('zone_id', $this->zone->id)
        ->assertCanSeeTableRecords([$region1])
        ->assertCanNotSeeTableRecords([$region2]);
});

it('can filter regions by level', function () {
    $region1 = Region::factory()->create(['level' => 1]);
    $region2 = Region::factory()->create(['level' => 2]);

    $this->actingAs($this->admin);

    Livewire::test('filament.resources.regions.list')
        ->filterTable('level', 1)
        ->assertCanSeeTableRecords([$region1])
        ->assertCanNotSeeTableRecords([$region2]);
});

it('can filter regions by enabled status', function () {
    $region1 = Region::factory()->create(['is_enabled' => true]);
    $region2 = Region::factory()->create(['is_enabled' => false]);

    $this->actingAs($this->admin);

    Livewire::test('filament.resources.regions.list')
        ->filterTable('is_enabled', true)
        ->assertCanSeeTableRecords([$region1])
        ->assertCanNotSeeTableRecords([$region2]);
});

it('can search regions', function () {
    $region1 = Region::factory()->create(['name' => 'Lithuania Region']);
    $region2 = Region::factory()->create(['name' => 'Germany Region']);

    $this->actingAs($this->admin);

    Livewire::test('filament.resources.regions.list')
        ->searchTable('Lithuania')
        ->assertCanSeeTableRecords([$region1])
        ->assertCanNotSeeTableRecords([$region2]);
});

it('validates required fields when creating region', function () {
    $this->actingAs($this->admin);

    Livewire::test('filament.resources.regions.create')
        ->fillForm([
            'name' => '', // required
            'country_id' => null, // required
            'level' => null, // required
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'country_id', 'level']);
});

it('validates unique code when provided', function () {
    $existingRegion = Region::factory()->create(['code' => 'TEST']);

    $this->actingAs($this->admin);

    Livewire::test('filament.resources.regions.create')
        ->fillForm([
            'name' => 'Test Region',
            'country_id' => $this->country->id,
            'code' => 'TEST', // duplicate
            'level' => 1,
        ])
        ->call('create')
        ->assertHasFormErrors(['code']);
});

it('can sort regions by name', function () {
    $region1 = Region::factory()->create(['name' => 'Zebra Region']);
    $region2 = Region::factory()->create(['name' => 'Alpha Region']);

    $this->actingAs($this->admin);

    Livewire::test('filament.resources.regions.list')
        ->sortTable('name')
        ->assertCanSeeTableRecords([$region2, $region1], inOrder: true);
});

it('can sort regions by level', function () {
    $region1 = Region::factory()->create(['level' => 2]);
    $region2 = Region::factory()->create(['level' => 1]);

    $this->actingAs($this->admin);

    Livewire::test('filament.resources.regions.list')
        ->sortTable('level')
        ->assertCanSeeTableRecords([$region2, $region1], inOrder: true);
});
