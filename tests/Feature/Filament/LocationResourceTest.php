<?php declare(strict_types=1);

use App\Filament\Resources\LocationResource;
use App\Models\Location;
use App\Models\Country;
use App\Models\User;
use function Pest\Laravel\{actingAs, assertDatabaseHas, assertDatabaseMissing};

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->country = Country::factory()->create();
});

it('can render location resource index page', function () {
    actingAs($this->admin)
        ->get(LocationResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render location resource create page', function () {
    actingAs($this->admin)
        ->get(LocationResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create location', function () {
    $newData = [
        'name' => 'Main Warehouse',
        'slug' => 'main-warehouse',
        'description' => 'Primary storage facility',
        'address' => '123 Main Street',
        'city' => 'Vilnius',
        'state' => 'Vilnius County',
        'postal_code' => '01234',
        'country_id' => $this->country->id,
        'phone' => '+370 600 12345',
        'email' => 'warehouse@example.com',
        'is_default' => false,
        'is_active' => true,
    ];

    actingAs($this->admin)
        ->post(LocationResource::getUrl('create'), $newData)
        ->assertRedirect();

    assertDatabaseHas('locations', $newData);
});

it('can render location resource view page', function () {
    $location = Location::factory()->create(['country_id' => $this->country->id]);

    actingAs($this->admin)
        ->get(LocationResource::getUrl('view', ['record' => $location]))
        ->assertSuccessful();
});

it('can render location resource edit page', function () {
    $location = Location::factory()->create(['country_id' => $this->country->id]);

    actingAs($this->admin)
        ->get(LocationResource::getUrl('edit', ['record' => $location]))
        ->assertSuccessful();
});

it('can update location', function () {
    $location = Location::factory()->create(['country_id' => $this->country->id]);
    $newData = [
        'name' => 'Updated Warehouse',
        'slug' => 'updated-warehouse',
        'city' => 'Kaunas',
        'is_active' => false,
    ];

    actingAs($this->admin)
        ->put(LocationResource::getUrl('edit', ['record' => $location]), array_merge($newData, [
            'country_id' => $this->country->id,
        ]))
        ->assertRedirect();

    assertDatabaseHas('locations', array_merge(['id' => $location->id], $newData));
});

it('can delete location', function () {
    $location = Location::factory()->create(['country_id' => $this->country->id]);

    actingAs($this->admin)
        ->delete(LocationResource::getUrl('edit', ['record' => $location]))
        ->assertRedirect();

    assertDatabaseMissing('locations', ['id' => $location->id]);
});

it('can list locations', function () {
    $locations = Location::factory()->count(5)->create(['country_id' => $this->country->id]);

    actingAs($this->admin)
        ->get(LocationResource::getUrl('index'))
        ->assertSuccessful()
        ->assertSeeText($locations->first()->name);
});

it('can filter active locations', function () {
    $activeLocation = Location::factory()->create(['is_active' => true, 'country_id' => $this->country->id]);
    $inactiveLocation = Location::factory()->create(['is_active' => false, 'country_id' => $this->country->id]);

    actingAs($this->admin)
        ->get(LocationResource::getUrl('index') . '?filter[active]=1')
        ->assertSuccessful()
        ->assertSeeText($activeLocation->name)
        ->assertDontSeeText($inactiveLocation->name);
});

it('can filter default locations', function () {
    $defaultLocation = Location::factory()->create(['is_default' => true, 'country_id' => $this->country->id]);
    $regularLocation = Location::factory()->create(['is_default' => false, 'country_id' => $this->country->id]);

    actingAs($this->admin)
        ->get(LocationResource::getUrl('index') . '?filter[default]=1')
        ->assertSuccessful()
        ->assertSeeText($defaultLocation->name)
        ->assertDontSeeText($regularLocation->name);
});

it('can filter locations by country', function () {
    $country2 = Country::factory()->create();
    $location1 = Location::factory()->create(['country_id' => $this->country->id]);
    $location2 = Location::factory()->create(['country_id' => $country2->id]);

    actingAs($this->admin)
        ->get(LocationResource::getUrl('index') . '?filter[country_id]=' . $this->country->id)
        ->assertSuccessful()
        ->assertSeeText($location1->name)
        ->assertDontSeeText($location2->name);
});

it('validates required fields when creating location', function () {
    actingAs($this->admin)
        ->post(LocationResource::getUrl('create'), [])
        ->assertSessionHasErrors(['name', 'slug', 'country_id']);
});

it('validates unique slug when creating location', function () {
    $existingLocation = Location::factory()->create(['slug' => 'existing-slug', 'country_id' => $this->country->id]);

    actingAs($this->admin)
        ->post(LocationResource::getUrl('create'), [
            'name' => 'Test Location',
            'slug' => 'existing-slug',
            'country_id' => $this->country->id,
        ])
        ->assertSessionHasErrors(['slug']);
});

it('validates email format when creating location', function () {
    actingAs($this->admin)
        ->post(LocationResource::getUrl('create'), [
            'name' => 'Test Location',
            'slug' => 'test-location',
            'country_id' => $this->country->id,
            'email' => 'invalid-email-format',
        ])
        ->assertSessionHasErrors(['email']);
});
