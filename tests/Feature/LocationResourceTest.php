<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_locations(): void
    {
        $country = Country::factory()->create(['name' => 'Lithuania']);
        $location = Location::factory()->create([
            'name' => 'Vilnius Store',
            'code' => 'VIL001',
            'country_code' => $country->cca2,
            'is_enabled' => true,
        ]);

        $this
            ->get('/admin/locations')
            ->assertOk()
            ->assertSee('Vilnius Store')
            ->assertSee('VIL001')
            ->assertSee('Lithuania');
    }

    public function test_can_create_location(): void
    {
        $country = Country::factory()->create();

        $this
            ->get('/admin/locations/create')
            ->assertOk();

        $this->post('/admin/locations', [
            'name' => 'Kaunas Warehouse',
            'code' => 'KAU001',
            'description' => 'Main warehouse in Kaunas',
            'country_code' => $country->cca2,
            'address_line_1' => 'Gedimino g. 1',
            'city' => 'Kaunas',
            'postal_code' => '44275',
            'phone' => '+370 37 123456',
            'email' => 'kaunas@example.com',
            'type' => 'warehouse',
            'latitude' => 54.8985,
            'longitude' => 23.9036,
            'is_enabled' => true,
            'is_default' => false,
            'sort_order' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('locations', [
            'name' => 'Kaunas Warehouse',
            'code' => 'KAU001',
            'description' => 'Main warehouse in Kaunas',
            'country_code' => $country->cca2,
            'address_line_1' => 'Gedimino g. 1',
            'city' => 'Kaunas',
            'postal_code' => '44275',
            'phone' => '+370 37 123456',
            'email' => 'kaunas@example.com',
            'type' => 'warehouse',
            'latitude' => 54.8985,
            'longitude' => 23.9036,
            'is_enabled' => true,
            'is_default' => false,
            'sort_order' => 1,
        ]);
    }

    public function test_can_view_location(): void
    {
        $country = Country::factory()->create();
        $location = Location::factory()->create([
            'name' => 'Riga Office',
            'country_code' => $country->cca2,
        ]);

        $this
            ->get("/admin/locations/{$location->id}")
            ->assertOk()
            ->assertSee('Riga Office');
    }

    public function test_can_edit_location(): void
    {
        $country = Country::factory()->create();
        $location = Location::factory()->create([
            'name' => 'Tallinn Store',
            'country_code' => $country->cca2,
        ]);

        $this
            ->get("/admin/locations/{$location->id}/edit")
            ->assertOk();

        $this->put("/admin/locations/{$location->id}", [
            'name' => 'Tallinn Main Store',
            'code' => 'TAL001',
            'description' => 'Updated description',
            'country_code' => $country->cca2,
            'address_line_1' => 'Narva mnt. 1',
            'city' => 'Tallinn',
            'postal_code' => '10117',
            'phone' => '+372 6 123456',
            'email' => 'tallinn@example.com',
            'type' => 'store',
            'latitude' => 59.437,
            'longitude' => 24.7536,
            'is_enabled' => false,
            'is_default' => true,
            'sort_order' => 2,
        ])->assertRedirect();

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Tallinn Main Store',
            'code' => 'TAL001',
            'description' => 'Updated description',
            'address_line_1' => 'Narva mnt. 1',
            'city' => 'Tallinn',
            'postal_code' => '10117',
            'phone' => '+372 6 123456',
            'email' => 'tallinn@example.com',
            'type' => 'store',
            'latitude' => 59.437,
            'longitude' => 24.7536,
            'is_enabled' => false,
            'is_default' => true,
            'sort_order' => 2,
        ]);
    }

    public function test_can_delete_location(): void
    {
        $country = Country::factory()->create();
        $location = Location::factory()->create([
            'country_code' => $country->cca2,
        ]);

        $this
            ->delete("/admin/locations/{$location->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('locations', [
            'id' => $location->id,
        ]);
    }

    public function test_can_filter_locations_by_country(): void
    {
        $country1 = Country::factory()->create(['name' => 'Lithuania']);
        $country2 = Country::factory()->create(['name' => 'Latvia']);

        Location::factory()->create([
            'name' => 'Vilnius Store',
            'country_code' => $country1->cca2,
        ]);

        Location::factory()->create([
            'name' => 'Riga Store',
            'country_code' => $country2->cca2,
        ]);

        $this
            ->get('/admin/locations?country_id='.$country1->id)
            ->assertOk()
            ->assertSee('Vilnius Store')
            ->assertDontSee('Riga Store');
    }

    public function test_can_filter_locations_by_type(): void
    {
        $country = Country::factory()->create();

        Location::factory()->create([
            'name' => 'Warehouse',
            'type' => 'warehouse',
            'country_code' => $country->cca2,
        ]);

        Location::factory()->create([
            'name' => 'Store',
            'type' => 'store',
            'country_code' => $country->cca2,
        ]);

        $this
            ->get('/admin/locations?type=warehouse')
            ->assertOk()
            ->assertSee('Warehouse')
            ->assertDontSee('Store');
    }

    public function test_can_filter_locations_by_enabled_status(): void
    {
        $country = Country::factory()->create();

        Location::factory()->create([
            'name' => 'Enabled Location',
            'is_enabled' => true,
            'country_code' => $country->cca2,
        ]);

        Location::factory()->create([
            'name' => 'Disabled Location',
            'is_enabled' => false,
            'country_code' => $country->cca2,
        ]);

        $this
            ->get('/admin/locations?is_enabled=1')
            ->assertOk()
            ->assertSee('Enabled Location')
            ->assertDontSee('Disabled Location');
    }

    public function test_can_filter_locations_by_default_status(): void
    {
        $country = Country::factory()->create();

        Location::factory()->create([
            'name' => 'Default Location',
            'is_default' => true,
            'country_code' => $country->cca2,
        ]);

        Location::factory()->create([
            'name' => 'Non-default Location',
            'is_default' => false,
            'country_code' => $country->cca2,
        ]);

        $this
            ->get('/admin/locations?is_default=1')
            ->assertOk()
            ->assertSee('Default Location')
            ->assertDontSee('Non-default Location');
    }

    public function test_can_filter_locations_by_coordinates(): void
    {
        $country = Country::factory()->create();

        Location::factory()->create([
            'name' => 'With Coordinates',
            'latitude' => 54.8985,
            'longitude' => 23.9036,
            'country_code' => $country->cca2,
        ]);

        Location::factory()->create([
            'name' => 'Without Coordinates',
            'latitude' => null,
            'longitude' => null,
            'country_code' => $country->cca2,
        ]);

        $this
            ->get('/admin/locations?has_coordinates=yes')
            ->assertOk()
            ->assertSee('With Coordinates')
            ->assertDontSee('Without Coordinates');
    }

    public function test_can_filter_locations_by_opening_hours(): void
    {
        $country = Country::factory()->create();

        Location::factory()->create([
            'name' => 'With Hours',
            'opening_hours' => [
                ['day' => 'monday', 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
            ],
            'country_code' => $country->cca2,
        ]);

        Location::factory()->create([
            'name' => 'Without Hours',
            'opening_hours' => null,
            'country_code' => $country->cca2,
        ]);

        $this
            ->get('/admin/locations?has_opening_hours=yes')
            ->assertOk()
            ->assertSee('With Hours')
            ->assertDontSee('Without Hours');
    }

    public function test_location_full_address_calculation(): void
    {
        $country = Country::factory()->create();
        $location = Location::factory()->create([
            'address_line_1' => 'Gedimino g. 1',
            'address_line_2' => 'Apt. 5',
            'city' => 'Vilnius',
            'state' => 'Vilnius County',
            'postal_code' => '01103',
            'country_code' => $country->cca2,
        ]);

        $expected = 'Gedimino g. 1, Apt. 5, Vilnius, Vilnius County, 01103';
        $this->assertEquals($expected, $location->full_address);
    }

    public function test_location_coordinates_calculation(): void
    {
        $country = Country::factory()->create();
        $location = Location::factory()->create([
            'latitude' => 54.8985,
            'longitude' => 23.9036,
            'country_code' => $country->cca2,
        ]);

        $this->assertEquals('54.8985, 23.9036', $location->coordinates);
    }

    public function test_location_google_maps_url(): void
    {
        $country = Country::factory()->create();
        $location = Location::factory()->create([
            'latitude' => 54.8985,
            'longitude' => 23.9036,
            'country_code' => $country->cca2,
        ]);

        $expected = 'https://www.google.com/maps?q=54.8985,23.9036';
        $this->assertEquals($expected, $location->google_maps_url);
    }

    public function test_location_type_checks(): void
    {
        $country = Country::factory()->create();

        $warehouse = Location::factory()->create([
            'type' => 'warehouse',
            'country_code' => $country->cca2,
        ]);

        $store = Location::factory()->create([
            'type' => 'store',
            'country_code' => $country->cca2,
        ]);

        $office = Location::factory()->create([
            'type' => 'office',
            'country_code' => $country->cca2,
        ]);

        $this->assertTrue($warehouse->isWarehouse());
        $this->assertFalse($warehouse->isStore());
        $this->assertFalse($warehouse->isOffice());

        $this->assertFalse($store->isWarehouse());
        $this->assertTrue($store->isStore());
        $this->assertFalse($store->isOffice());

        $this->assertFalse($office->isWarehouse());
        $this->assertFalse($office->isStore());
        $this->assertTrue($office->isOffice());
    }

    public function test_location_coordinates_check(): void
    {
        $country = Country::factory()->create();

        $withCoords = Location::factory()->create([
            'latitude' => 54.8985,
            'longitude' => 23.9036,
            'country_code' => $country->cca2,
        ]);

        $withoutCoords = Location::factory()->create([
            'latitude' => null,
            'longitude' => null,
            'country_code' => $country->cca2,
        ]);

        $this->assertTrue($withCoords->hasCoordinates());
        $this->assertFalse($withoutCoords->hasCoordinates());
    }

    public function test_location_opening_hours_check(): void
    {
        $country = Country::factory()->create();

        $withHours = Location::factory()->create([
            'opening_hours' => [
                ['day' => 'monday', 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
            ],
            'country_code' => $country->cca2,
        ]);

        $withoutHours = Location::factory()->create([
            'opening_hours' => null,
            'country_code' => $country->cca2,
        ]);

        $this->assertTrue($withHours->hasOpeningHours());
        $this->assertFalse($withoutHours->hasOpeningHours());
    }

    public function test_location_opening_hours_for_day(): void
    {
        $country = Country::factory()->create();
        $location = Location::factory()->create([
            'opening_hours' => [
                ['day' => 'monday', 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
                ['day' => 'tuesday', 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
            ],
            'country_code' => $country->cca2,
        ]);

        $mondayHours = $location->getOpeningHoursForDay('monday');
        $this->assertNotNull($mondayHours);
        $this->assertEquals('09:00', $mondayHours['open_time']);
        $this->assertEquals('18:00', $mondayHours['close_time']);

        $sundayHours = $location->getOpeningHoursForDay('sunday');
        $this->assertNull($sundayHours);
    }

    public function test_location_formatted_opening_hours(): void
    {
        $country = Country::factory()->create();
        $location = Location::factory()->create([
            'opening_hours' => [
                ['day' => 'monday', 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
                ['day' => 'tuesday', 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
            ],
            'country_code' => $country->cca2,
        ]);

        $formatted = $location->getFormattedOpeningHours();
        $this->assertArrayHasKey('monday', $formatted);
        $this->assertArrayHasKey('tuesday', $formatted);
        $this->assertEquals('09:00', $formatted['monday']['open_time']);
        $this->assertEquals('18:00', $formatted['monday']['close_time']);
    }

    public function test_location_business_info(): void
    {
        $country = Country::factory()->create();
        $location = Location::factory()->create([
            'type' => 'warehouse',
            'latitude' => 54.8985,
            'longitude' => 23.9036,
            'opening_hours' => [
                ['day' => 'monday', 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
            ],
            'country_code' => $country->cca2,
        ]);

        $businessInfo = $location->getBusinessInfo();
        $this->assertEquals('warehouse', $businessInfo['type']);
        $this->assertTrue($businessInfo['is_warehouse']);
        $this->assertFalse($businessInfo['is_store']);
        $this->assertFalse($businessInfo['is_office']);
        $this->assertTrue($businessInfo['has_coordinates']);
        $this->assertTrue($businessInfo['has_opening_hours']);
    }

    public function test_location_complete_info(): void
    {
        $country = Country::factory()->create();
        $location = Location::factory()->create([
            'name' => 'Test Location',
            'description' => 'Test Description',
            'type' => 'store',
            'country_code' => $country->cca2,
        ]);

        $completeInfo = $location->getCompleteInfo();
        $this->assertArrayHasKey('basic', $completeInfo);
        $this->assertArrayHasKey('location', $completeInfo);
        $this->assertArrayHasKey('business', $completeInfo);
        $this->assertArrayHasKey('status', $completeInfo);
        $this->assertEquals('Test Location', $completeInfo['basic']['name']);
    }
}
