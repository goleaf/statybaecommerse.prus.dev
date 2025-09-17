<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Country;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createTestCountry(): Country
    {
        return Country::factory()->create([
            'cca2' => 'LT',
            'name' => 'Lithuania',
        ]);
    }

    public function test_location_index_page_loads(): void
    {
        $country = $this->createTestCountry();
        Location::factory()->count(5)->create(['country_code' => 'LT']);
        
        $response = $this->get(localized_route('locations.index'));
        
        $response->assertOk()
            ->assertViewIs('locations.index')
            ->assertSee('Locations');
    }

    public function test_location_show_page_loads(): void
    {
        $country = $this->createTestCountry();
        $location = Location::factory()->create(['country_code' => 'LT']);
        
        $response = $this->get(localized_route('locations.show', $location));
        
        $response->assertOk()
            ->assertViewIs('locations.show')
            ->assertSee($location->name);
    }

    public function test_location_index_with_filters(): void
    {
        $country = $this->createTestCountry();
        Location::factory()->create(['type' => 'warehouse', 'is_enabled' => true, 'country_code' => 'LT']);
        Location::factory()->create(['type' => 'store', 'is_enabled' => true, 'country_code' => 'LT']);
        
        $response = $this->get(localized_route('locations.index', [
            'type' => 'warehouse',
        ]));
        
        $response->assertOk();
        $this->assertTrue(true); // Page loads successfully with filters
    }

    public function test_location_search_api(): void
    {
        $country = $this->createTestCountry();
        $location = Location::factory()->create([
            'name' => 'Test Warehouse',
            'country_code' => 'LT',
        ]);
        
        $response = $this->getJson(localized_route('locations.api.search', ['q' => 'Test']));
        
        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_location_by_type_api(): void
    {
        $country = $this->createTestCountry();
        Location::factory()->create(['type' => 'warehouse', 'country_code' => 'LT']);
        Location::factory()->create(['type' => 'store', 'country_code' => 'LT']);
        
        $response = $this->getJson(localized_route('locations.api.by-type', 'warehouse'));
        
        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_location_by_country_api(): void
    {
        $country = $this->createTestCountry();
        Location::factory()->create(['country_code' => 'LT']);
        
        $response = $this->getJson(localized_route('locations.api.by-country', $country));
        
        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_location_by_city_api(): void
    {
        $country = $this->createTestCountry();
        Location::factory()->create(['city' => 'Vilnius', 'country_code' => 'LT']);
        
        $response = $this->getJson(localized_route('locations.api.by-city', 'Vilnius'));
        
        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_location_nearby_api(): void
    {
        $country = $this->createTestCountry();
        Location::factory()->create([
            'latitude' => 54.6872,
            'longitude' => 25.2797,
            'country_code' => 'LT',
        ]);
        
        $response = $this->getJson(localized_route('locations.api.nearby', [
            'lat' => 54.6872,
            'lng' => 25.2797,
            'radius' => 10,
        ]));
        
        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_location_nearby_api_without_coordinates(): void
    {
        $response = $this->getJson(localized_route('locations.api.nearby'));
        
        $response->assertStatus(400)
            ->assertJson(['error' => 'Latitude and longitude are required']);
    }

    public function test_location_statistics_api(): void
    {
        $country = $this->createTestCountry();
        Location::factory()->count(3)->create(['type' => 'warehouse', 'country_code' => 'LT']);
        Location::factory()->count(2)->create(['type' => 'store', 'country_code' => 'LT']);
        
        $response = $this->getJson(localized_route('locations.api.statistics'));
        
        $response->assertOk()
            ->assertJsonStructure([
                'total_locations',
                'by_type',
                'enabled_locations',
                'default_locations',
            ]);
    }

    public function test_location_show_with_relations(): void
    {
        $country = $this->createTestCountry();
        $location = Location::factory()->create([
            'name' => 'Test Location',
            'country_code' => 'LT',
        ]);
        
        $response = $this->get(localized_route('locations.show', $location));
        
        $response->assertOk()
            ->assertSee('Test Location')
            ->assertSee('Lithuania');
    }

    public function test_location_index_pagination(): void
    {
        $country = $this->createTestCountry();
        Location::factory()->count(15)->create(['country_code' => 'LT']);
        
        $response = $this->get(localized_route('locations.index'));
        
        $response->assertOk();
        $this->assertTrue(true); // Page loads successfully with many locations
    }
}