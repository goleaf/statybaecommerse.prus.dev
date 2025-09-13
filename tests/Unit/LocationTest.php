<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Country;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\VariantInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create countries for foreign key constraints
        \App\Models\Country::create([
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
            'is_active' => true,
            'is_enabled' => true,
        ]);
        
        \App\Models\Country::create([
            'name' => 'United States',
            'cca2' => 'US',
            'cca3' => 'USA',
            'is_active' => true,
            'is_enabled' => true,
        ]);
        
        \App\Models\Country::create([
            'name' => 'United Kingdom',
            'cca2' => 'GB',
            'cca3' => 'GBR',
            'is_active' => true,
            'is_enabled' => true,
        ]);
    }

    public function test_location_can_be_created(): void
    {
        $location = new Location([
            'code' => 'WH001',
            'name' => 'Test Warehouse',
            'slug' => 'test-warehouse',
            'type' => 'warehouse',
            'country_code' => 'LT',
            'is_enabled' => true,
            'is_default' => false,
            'sort_order' => 0,
        ]);
        
        $location->save();

        $this->assertDatabaseHas('locations', [
            'name' => 'Test Warehouse',
            'code' => 'WH001',
            'type' => 'warehouse',
            'is_enabled' => true,
        ]);

        $this->assertEquals('Test Warehouse', $location->name);
        $this->assertEquals('WH001', $location->code);
        $this->assertEquals('warehouse', $location->type);
        $this->assertTrue($location->is_enabled);
    }

    public function test_location_belongs_to_country(): void
    {
        $country = Country::factory()->create(['cca2' => 'LT']);
        $location = Location::factory()->create(['country_code' => 'LT']);

        $this->assertInstanceOf(Country::class, $location->country);
        $this->assertEquals('LT', $location->country->cca2);
    }

    public function test_location_has_many_inventories(): void
    {
        $location = Location::factory()->create();
        $inventory = Inventory::factory()->create(['location_id' => $location->id]);

        $this->assertTrue($location->inventories->contains($inventory));
        $this->assertInstanceOf(Inventory::class, $location->inventories->first());
    }

    public function test_location_has_many_variant_inventories(): void
    {
        $location = Location::factory()->create();
        $variantInventory = VariantInventory::factory()->create(['location_id' => $location->id]);

        $this->assertTrue($location->variantInventories->contains($variantInventory));
        $this->assertInstanceOf(VariantInventory::class, $location->variantInventories->first());
    }

    public function test_location_scope_enabled(): void
    {
        Location::factory()->create(['is_enabled' => true]);
        Location::factory()->create(['is_enabled' => false]);

        $enabledLocations = Location::enabled()->get();

        $this->assertCount(1, $enabledLocations);
        $this->assertTrue($enabledLocations->first()->is_enabled);
    }

    public function test_location_scope_default(): void
    {
        Location::factory()->create(['is_default' => true]);
        Location::factory()->create(['is_default' => false]);

        $defaultLocations = Location::default()->get();

        $this->assertCount(1, $defaultLocations);
        $this->assertTrue($defaultLocations->first()->is_default);
    }

    public function test_location_scope_by_type(): void
    {
        Location::factory()->create(['type' => 'warehouse']);
        Location::factory()->create(['type' => 'store']);

        $warehouses = Location::byType('warehouse')->get();

        $this->assertCount(1, $warehouses);
        $this->assertEquals('warehouse', $warehouses->first()->type);
    }

    public function test_location_full_address_attribute(): void
    {
        $location = Location::factory()->create([
            'address_line_1' => '123 Main St',
            'address_line_2' => 'Suite 100',
            'city' => 'Vilnius',
            'state' => 'Vilniaus',
            'postal_code' => '01101',
        ]);

        $expected = '123 Main St, Suite 100, Vilnius, Vilniaus, 01101';
        $this->assertEquals($expected, $location->full_address);
    }

    public function test_location_full_address_with_empty_fields(): void
    {
        $location = Location::factory()->create([
            'address_line_1' => '123 Main St',
            'address_line_2' => null,
            'city' => 'Vilnius',
            'state' => null,
            'postal_code' => '01101',
        ]);

        $expected = '123 Main St, Vilnius, 01101';
        $this->assertEquals($expected, $location->full_address);
    }

    public function test_location_type_label_attribute(): void
    {
        $warehouse = Location::factory()->create(['type' => 'warehouse']);
        $store = Location::factory()->create(['type' => 'store']);

        $this->assertEquals(__('locations.type_warehouse'), $warehouse->type_label);
        $this->assertEquals(__('locations.type_store'), $store->type_label);
    }

    public function test_location_coordinates_attribute(): void
    {
        $location = Location::factory()->create([
            'latitude' => 54.6872,
            'longitude' => 25.2797,
        ]);

        $this->assertEquals('54.6872, 25.2797', $location->coordinates);
    }

    public function test_location_coordinates_attribute_with_null_values(): void
    {
        $location = Location::factory()->create([
            'latitude' => null,
            'longitude' => null,
        ]);

        $this->assertNull($location->coordinates);
    }

    public function test_location_google_maps_url_attribute(): void
    {
        $location = Location::factory()->create([
            'latitude' => 54.6872,
            'longitude' => 25.2797,
        ]);

        $expected = 'https://www.google.com/maps?q=54.6872,25.2797';
        $this->assertEquals($expected, $location->google_maps_url);
    }

    public function test_location_google_maps_url_with_null_values(): void
    {
        $location = Location::factory()->create([
            'latitude' => null,
            'longitude' => null,
        ]);

        $this->assertNull($location->google_maps_url);
    }

    public function test_location_type_checker_methods(): void
    {
        $warehouse = Location::factory()->create(['type' => 'warehouse']);
        $store = Location::factory()->create(['type' => 'store']);
        $office = Location::factory()->create(['type' => 'office']);
        $other = Location::factory()->create(['type' => 'other']);

        $this->assertTrue($warehouse->isWarehouse());
        $this->assertFalse($warehouse->isStore());

        $this->assertTrue($store->isStore());
        $this->assertFalse($store->isWarehouse());

        $this->assertTrue($office->isOffice());
        $this->assertTrue($other->isOther());
    }

    public function test_location_has_coordinates(): void
    {
        $locationWithCoords = Location::factory()->create([
            'latitude' => 54.6872,
            'longitude' => 25.2797,
        ]);

        $locationWithoutCoords = Location::factory()->create([
            'latitude' => null,
            'longitude' => null,
        ]);

        $this->assertTrue($locationWithCoords->hasCoordinates());
        $this->assertFalse($locationWithoutCoords->hasCoordinates());
    }

    public function test_location_has_opening_hours(): void
    {
        $locationWithHours = Location::factory()->create([
            'opening_hours' => [
                ['day' => 'monday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
            ],
        ]);

        $locationWithoutHours = Location::factory()->create([
            'opening_hours' => null,
        ]);

        $this->assertTrue($locationWithHours->hasOpeningHours());
        $this->assertFalse($locationWithoutHours->hasOpeningHours());
    }

    public function test_location_get_opening_hours_for_day(): void
    {
        $location = Location::factory()->create([
            'opening_hours' => [
                ['day' => 'monday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                ['day' => 'tuesday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
            ],
        ]);

        $mondayHours = $location->getOpeningHoursForDay('monday');
        $sundayHours = $location->getOpeningHoursForDay('sunday');

        $this->assertNotNull($mondayHours);
        $this->assertEquals('monday', $mondayHours['day']);
        $this->assertEquals('09:00', $mondayHours['open_time']);
        $this->assertEquals('17:00', $mondayHours['close_time']);

        $this->assertNull($sundayHours);
    }

    public function test_location_get_formatted_opening_hours(): void
    {
        $location = Location::factory()->create([
            'opening_hours' => [
                ['day' => 'monday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                ['day' => 'sunday', 'open_time' => null, 'close_time' => null, 'is_closed' => true],
            ],
        ]);

        $formattedHours = $location->getFormattedOpeningHours();

        $this->assertArrayHasKey('monday', $formattedHours);
        $this->assertArrayHasKey('sunday', $formattedHours);

        $this->assertEquals(__('locations.monday'), $formattedHours['monday']['day']);
        $this->assertEquals('09:00', $formattedHours['monday']['open_time']);
        $this->assertEquals('17:00', $formattedHours['monday']['close_time']);
        $this->assertFalse($formattedHours['monday']['is_closed']);

        $this->assertEquals(__('locations.sunday'), $formattedHours['sunday']['day']);
        $this->assertTrue($formattedHours['sunday']['is_closed']);
    }

    public function test_location_soft_deletes(): void
    {
        $location = Location::factory()->create();
        $locationId = $location->id;

        $location->delete();

        $this->assertSoftDeleted('locations', ['id' => $locationId]);
        $this->assertDatabaseHas('locations', ['id' => $locationId]);
    }

    public function test_location_fillable_attributes(): void
    {
        $fillable = [
            'name',
            'slug',
            'description',
            'code',
            'address_line_1',
            'address_line_2',
            'city',
            'state',
            'postal_code',
            'country_code',
            'phone',
            'email',
            'is_enabled',
            'is_default',
            'type',
            'latitude',
            'longitude',
            'opening_hours',
            'contact_info',
            'sort_order',
        ];

        $location = new Location();
        $this->assertEquals($fillable, $location->getFillable());
    }

    public function test_location_casts(): void
    {
        $location = Location::factory()->create([
            'is_enabled' => '1',
            'is_default' => '0',
            'latitude' => '54.6872',
            'longitude' => '25.2797',
            'opening_hours' => ['monday' => '09:00-17:00'],
            'contact_info' => ['phone' => '+37012345678'],
            'sort_order' => '10',
        ]);

        $this->assertIsBool($location->is_enabled);
        $this->assertIsBool($location->is_default);
        $this->assertIsFloat($location->latitude);
        $this->assertIsFloat($location->longitude);
        $this->assertIsArray($location->opening_hours);
        $this->assertIsArray($location->contact_info);
        $this->assertIsInt($location->sort_order);
    }
}
