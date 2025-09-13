<?php declare(strict_types=1);

use App\Models\Location;
use App\Models\User;
use App\Models\Country;
use Livewire\Livewire;
use App\Filament\Resources\LocationResource;
use App\Filament\Resources\LocationResource\Pages\ListLocations;
use App\Filament\Resources\LocationResource\Pages\CreateLocation;
use App\Filament\Resources\LocationResource\Pages\ViewLocation;
use App\Filament\Resources\LocationResource\Pages\EditLocation;

beforeEach(function () {
    $this->adminUser = User::factory()->create(['is_admin' => true]);
});

it('can list locations in admin panel', function () {
    $location = Location::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(ListLocations::class)
        ->assertCanSeeTableRecords([$location]);
});

it('can create a new location', function () {
    $locationData = [
        'code' => 'LOC001',
        'name' => 'Test Location',
        'address_line_1' => '123 Test Street',
        'city' => 'Test City',
        'state' => 'Test State',
        'postal_code' => '12345',
        'country_code' => 'LT',
        'phone' => '+1234567890',
        'email' => 'test@location.com',
        'is_enabled' => true,
    ];
    
    Livewire::actingAs($this->adminUser)
        ->test(CreateLocation::class)
        ->fillForm($locationData)
        ->call('create')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('locations', [
        'code' => 'LOC001',
        'address_line_1' => '123 Test Street',
        'city' => 'Test City',
        'state' => 'Test State',
        'postal_code' => '12345',
        'country_code' => 'LT',
        'phone' => '+1234567890',
        'email' => 'test@location.com',
        'is_enabled' => true,
    ]);
});

it('can view a location', function () {
    $location = Location::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(ViewLocation::class, ['record' => $location->id])
        ->assertOk();
});

it('can edit a location', function () {
    $location = Location::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(EditLocation::class, ['record' => $location->id])
        ->fillForm([
            'address_line_1' => '456 Updated Street',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'address_line_1' => '456 Updated Street',
    ]);
});

it('can delete a location', function () {
    $location = Location::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(ListLocations::class)
        ->callTableAction('delete', $location)
        ->assertHasNoTableActionErrors();
    
    $this->assertSoftDeleted('locations', [
        'id' => $location->id,
    ]);
});

it('validates required fields when creating location', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateLocation::class)
        ->fillForm([
            'code' => null,
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['code', 'name']);
});

it('validates unique location code', function () {
    $existingLocation = Location::factory()->create(['code' => 'UNIQUE']);
    
    Livewire::actingAs($this->adminUser)
        ->test(CreateLocation::class)
        ->fillForm([
            'code' => 'UNIQUE',
            'name' => 'Another Location',
        ])
        ->call('create')
        ->assertHasFormErrors(['code']);
});

it('validates email format', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateLocation::class)
        ->fillForm([
            'code' => 'LOC001',
            'name' => 'Test Location',
            'email' => 'invalid-email',
        ])
        ->call('create')
        ->assertHasFormErrors(['email']);
});

it('can filter locations by country code', function () {
    $location1 = Location::factory()->create(['country_code' => 'LT']);
    $location2 = Location::factory()->create(['country_code' => 'LV']);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListLocations::class)
        ->searchTable('LT')
        ->assertCanSeeTableRecords([$location1])
        ->assertCanNotSeeTableRecords([$location2]);
});

it('can filter locations by enabled status', function () {
    $enabledLocation = Location::factory()->create(['is_enabled' => true]);
    $disabledLocation = Location::factory()->create(['is_enabled' => false]);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListLocations::class)
        ->filterTable('is_enabled', true)
        ->assertCanSeeTableRecords([$enabledLocation])
        ->assertCanNotSeeTableRecords([$disabledLocation]);
});

it('shows correct location data in table', function () {
    $location = Location::factory()->create([
        'code' => 'LOC001',
        'city' => 'Vilnius',
        'state' => 'Vilnius County',
        'country_code' => 'LT',
        'is_enabled' => true,
    ]);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListLocations::class)
        ->assertCanSeeTableRecords([$location])
        ->assertCanRenderTableColumn('code')
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('city')
        ->assertCanRenderTableColumn('state')
        ->assertCanRenderTableColumn('country_code')
        ->assertCanRenderTableColumn('is_enabled');
});

it('handles location activation and deactivation', function () {
    $location = Location::factory()->create(['is_enabled' => false]);
    
    Livewire::actingAs($this->adminUser)
        ->test(EditLocation::class, ['record' => $location->id])
        ->fillForm(['is_enabled' => true])
        ->call('save')
        ->assertHasNoFormErrors();
        
    expect($location->fresh()->is_enabled)->toBeTrue();
});

it('can search locations by name', function () {
    $location1 = Location::factory()->create(['name' => 'Vilnius Warehouse']);
    $location2 = Location::factory()->create(['name' => 'Kaunas Store']);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListLocations::class)
        ->searchTable('Vilnius')
        ->assertCanSeeTableRecords([$location1])
        ->assertCanNotSeeTableRecords([$location2]);
});

it('can search locations by code', function () {
    $location1 = Location::factory()->create(['code' => 'VIL001']);
    $location2 = Location::factory()->create(['code' => 'KAU001']);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListLocations::class)
        ->searchTable('VIL')
        ->assertCanSeeTableRecords([$location1])
        ->assertCanNotSeeTableRecords([$location2]);
});

it('can search locations by city', function () {
    $location1 = Location::factory()->create(['city' => 'Vilnius']);
    $location2 = Location::factory()->create(['city' => 'Kaunas']);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListLocations::class)
        ->searchTable('Vilnius')
        ->assertCanSeeTableRecords([$location1])
        ->assertCanNotSeeTableRecords([$location2]);
});

it('handles bulk actions on locations', function () {
    $location1 = Location::factory()->create();
    $location2 = Location::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(ListLocations::class)
        ->callTableBulkAction('delete', [$location1->id, $location2->id])
        ->assertOk();
    
    $this->assertSoftDeleted('locations', [
        'id' => $location1->id,
    ]);
    
    $this->assertSoftDeleted('locations', [
        'id' => $location2->id,
    ]);
});

it('can create location with minimal required fields', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateLocation::class)
        ->fillForm([
            'code' => 'MIN001',
            'name' => 'Minimal Location',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('locations', [
        'code' => 'MIN001',
    ]);
});

it('can set phone number', function () {
    $location = Location::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(EditLocation::class, ['record' => $location->id])
        ->fillForm(['phone' => '+37060012345'])
        ->call('save')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'phone' => '+37060012345',
    ]);
});

it('can set postal code', function () {
    $location = Location::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(EditLocation::class, ['record' => $location->id])
        ->fillForm(['postal_code' => 'LT-01101'])
        ->call('save')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'postal_code' => 'LT-01101',
    ]);
});

it('can set country code for location', function () {
    $location = Location::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(EditLocation::class, ['record' => $location->id])
        ->fillForm(['country_code' => 'LT'])
        ->call('save')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'country_code' => 'LT',
    ]);
});
