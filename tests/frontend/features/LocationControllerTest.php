<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

final class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_locations_index_page_loads(): void
    {
        Location::factory()->count(3)->create(['is_enabled' => true]);

        $response = $this->get(route('locations.index'));

        $response->assertStatus(200);
        $response->assertViewIs('locations.index');
        $response->assertViewHas('locations');
        $response->assertViewHas('types');
        $response->assertViewHas('cities');
    }

    public function test_locations_index_shows_only_enabled_locations(): void
    {
        Location::factory()->create([
            'is_enabled' => true,
            'name'       => 'Enabled Location',
            'latitude'   => 54.6872,
            'longitude'  => 25.2797,
        ]);
        Location::factory()->create(['is_enabled' => false, 'name' => 'Disabled Location']);

        $response = $this->get(route('locations.index'));

        $response->assertStatus(200);
        $response->assertSee('Enabled Location');
        $response->assertDontSee('Disabled Location');
    }

    public function test_locations_index_filters_by_type(): void
    {
        Location::factory()->create([
            'type'      => 'warehouse',
            'name'      => 'Warehouse Location',
            'latitude'  => 54.6872,
            'longitude' => 25.2797,
        ]);
        Location::factory()->create([
            'type'      => 'store',
            'name'      => 'Store Location',
            'latitude'  => 55.1739,
            'longitude' => 24.2799,
        ]);

        $response = $this->get(route('locations.index', ['type' => 'warehouse']));

        $response->assertStatus(200);
        $response->assertSee('Warehouse Location');
        $response->assertDontSee('Store Location');
    }

    public function test_locations_index_filters_by_city(): void
    {
        Location::factory()->create([
            'city'      => 'Vilnius',
            'name'      => 'Vilnius Location',
            'latitude'  => 54.6872,
            'longitude' => 25.2797,
        ]);
        Location::factory()->create([
            'city'      => 'Kaunas',
            'name'      => 'Kaunas Location',
            'latitude'  => 54.8985,
            'longitude' => 23.9036,
        ]);

        $response = $this->get(route('locations.index', ['city' => 'Vilnius']));

        $response->assertStatus(200);
        $response->assertSee('Vilnius Location');
        $response->assertDontSee('Kaunas Location');
    }

    public function test_locations_index_searches_by_name(): void
    {
        Location::factory()->create([
            'name'      => 'Main Warehouse',
            'latitude'  => 54.6872,
            'longitude' => 25.2797,
        ]);
        Location::factory()->create([
            'name'      => 'Secondary Store',
            'latitude'  => 55.1739,
            'longitude' => 24.2799,
        ]);

        $response = $this->get(route('locations.index', ['search' => 'Main']));

        $response->assertStatus(200);
        $response->assertSee('Main Warehouse');
        $response->assertDontSee('Secondary Store');
    }

    public function test_locations_index_searches_by_address(): void
    {
        Location::factory()->create([
            'address_line_1' => '123 Main Street',
            'latitude'       => 54.6872,
            'longitude'      => 25.2797,
        ]);
        Location::factory()->create([
            'address_line_1' => '456 Oak Avenue',
            'latitude'       => 55.1739,
            'longitude'      => 24.2799,
        ]);

        $response = $this->get(route('locations.index', ['search' => 'Main']));

        $response->assertStatus(200);
        $response->assertSee('123 Main Street');
        $response->assertDontSee('456 Oak Avenue');
    }

    public function test_locations_show_page_loads(): void
    {
        $location = Location::factory()->create([
            'is_enabled' => true,
            'latitude'   => 54.6872,
            'longitude'  => 25.2797,
        ]);

        $response = $this->get(route('locations.show', $location));

        $response->assertStatus(200);
        $response->assertViewIs('locations.show');
        $response->assertViewHas('location', $location);
        $response->assertViewHas('relatedLocations');
    }

    public function test_locations_show_returns_404_for_disabled_location(): void
    {
        $location = Location::factory()->create([
            'is_enabled' => false,
            'latitude'   => 54.6872,
            'longitude'  => 25.2797,
        ]);

        $response = $this->get(route('locations.show', $location));

        $response->assertStatus(404);
    }

    public function test_locations_show_displays_location_information(): void
    {
        $country = Country::factory()->create(['cca2' => 'LT', 'name' => 'Lithuania']);
        $location = Location::factory()->create([
            'is_enabled'     => true,
            'name'           => 'Test Location',
            'address_line_1' => '123 Test Street',
            'city'           => 'Test City',
            'phone'          => '+37012345678',
            'email'          => 'test@example.com',
            'country_code'   => 'LT',
            'latitude'       => 54.6872,
            'longitude'      => 25.2797,
        ]);

        $response = $this->get(route('locations.show', $location));

        $response->assertStatus(200);
        $response->assertSee('Test Location');
        $response->assertSee('123 Test Street');
        $response->assertSee('Test City');
        $response->assertSee('+37012345678');
        $response->assertSee('test@example.com');
    }

    public function test_locations_show_displays_related_locations(): void
    {
        $mainLocation = Location::factory()->create([
            'is_enabled' => true,
            'type'       => 'warehouse',
            'city'       => 'Vilnius',
            'latitude'   => 54.6872,
            'longitude'  => 25.2797,
        ]);

        $relatedLocation1 = Location::factory()->create([
            'is_enabled' => true,
            'type'       => 'warehouse',
            'city'       => 'Vilnius',
            'latitude'   => 54.6875,
            'longitude'  => 25.2801,
        ]);

        $relatedLocation2 = Location::factory()->create([
            'is_enabled' => true,
            'type'       => 'store',
            'city'       => 'Vilnius',
            'latitude'   => 54.688,
            'longitude'  => 25.2805,
        ]);

        $unrelatedLocation = Location::factory()->create([
            'is_enabled' => true,
            'type'       => 'warehouse',
            'city'       => 'Kaunas',
            'latitude'   => 54.8985,
            'longitude'  => 23.9036,
        ]);

        $response = $this->get(route('locations.show', $mainLocation));

        $response->assertStatus(200);
        $response->assertSee($relatedLocation1->name);
        $response->assertSee($relatedLocation2->name);
        $response->assertDontSee($unrelatedLocation->name);
    }

    public function test_locations_contact_form_submission(): void
    {
        $location = Location::factory()->create([
            'is_enabled' => true,
            'latitude'   => 54.6872,
            'longitude'  => 25.2797,
        ]);

        $response = $this->post(route('locations.contact', $location), [
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('locations.contact_success'));
    }

    public function test_locations_contact_form_validation(): void
    {
        $location = Location::factory()->create([
            'is_enabled' => true,
            'latitude'   => 54.6872,
            'longitude'  => 25.2797,
        ]);

        $response = $this->post(route('locations.contact', $location), [
            'name'    => '',
            'email'   => 'invalid-email',
            'subject' => '',
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_locations_contact_form_requires_valid_email(): void
    {
        $location = Location::factory()->create([
            'is_enabled' => true,
            'latitude'   => 54.6872,
            'longitude'  => 25.2797,
        ]);

        $response = $this->post(route('locations.contact', $location), [
            'name'    => 'John Doe',
            'email'   => 'not-an-email',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_locations_contact_form_limits_message_length(): void
    {
        $location = Location::factory()->create([
            'is_enabled' => true,
            'latitude'   => 54.6872,
            'longitude'  => 25.2797,
        ]);

        $response = $this->post(route('locations.contact', $location), [
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => str_repeat('a', 1001), // Exceeds 1000 character limit
        ]);

        $response->assertSessionHasErrors(['message']);
    }

    public function test_locations_index_pagination(): void
    {
        Location::factory()->count(15)->create(['is_enabled' => true]);

        $response = $this->get(route('locations.index'));

        $response->assertStatus(200);
        $response->assertViewHas('locations');

        $locations = $response->viewData('locations');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $locations);
    }

    public function test_locations_index_preserves_filters_in_pagination(): void
    {
        Location::factory()->create([
            'type'      => 'warehouse',
            'name'      => 'Warehouse 1',
            'latitude'  => 54.6872,
            'longitude' => 25.2797,
        ]);
        Location::factory()->create([
            'type'      => 'warehouse',
            'name'      => 'Warehouse 2',
            'latitude'  => 54.6882,
            'longitude' => 25.2801,
        ]);
        Location::factory()->create([
            'type'      => 'store',
            'name'      => 'Store 1',
            'latitude'  => 55.1739,
            'longitude' => 24.2799,
        ]);

        $response = $this->get(route('locations.index', ['type' => 'warehouse', 'page' => 1]));

        $response->assertStatus(200);
        $response->assertSee('Warehouse 1');
        $response->assertSee('Warehouse 2');
        $response->assertDontSee('Store 1');
    }

    public function test_locations_show_displays_opening_hours(): void
    {
        $location = Location::factory()->create([
            'is_enabled'    => true,
            'opening_hours' => [
                ['day' => 'monday', 'open_time' => '09:00', 'close_time' => '17:00', 'is_closed' => false],
                ['day' => 'sunday', 'open_time' => null, 'close_time' => null, 'is_closed' => true],
            ],
        ]);

        $response = $this->get(route('locations.show', $location));

        $response->assertStatus(200);
        $response->assertSee(__('locations.working_hours'));
        $response->assertSee(__('locations.monday'));
        $response->assertSee('09:00');
        $response->assertSee('17:00');
    }

    public function test_locations_show_displays_google_maps_link(): void
    {
        $location = Location::factory()->create([
            'is_enabled' => true,
            'latitude'   => 54.6872,
            'longitude'  => 25.2797,
        ]);

        $response = $this->get(route('locations.show', $location));

        $response->assertStatus(200);
        $response->assertSee('https://www.google.com/maps?q=54.6872,25.2797');
        $response->assertSee(__('locations.get_directions'));
    }

    public function test_locations_api_filters_incomplete_records(): void
    {
        // Create a fully populated location that should appear in the API response.
        $visibleLocation = Location::factory()->create([
            'is_enabled'   => true,
            'name'         => 'Visible Depot',
            'type'         => 'warehouse',
            'city'         => 'Vilnius',
            'country_code' => 'LT',
            'latitude'     => 54.6872,
            'longitude'    => 25.2797,
        ]);

        // Seed a location with missing metadata to ensure the controller filters it out.
        Location::factory()->create([
            'is_enabled'   => true,
            'name'         => 'Incomplete Hub',
            'type'         => '',
            'city'         => 'Kaunas',
            'country_code' => 'LT',
            'latitude'     => 54.8985,
            'longitude'    => 23.9036,
        ]);

        $response = $this->getJson(route('locations.api.index'));

        $response
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('total', 1)
                ->has('locations', 1, fn (AssertableJson $locationJson) => $locationJson
                    ->where('id', $visibleLocation->id)
                    ->where('name', 'Visible Depot')
                    ->etc()
                )
            );
    }

    public function test_locations_api_excludes_records_without_coordinates(): void
    {
        // Seed a location that would otherwise be visible but lacks coordinate data.
        Location::factory()->create([
            'is_enabled'   => true,
            'name'         => 'Coordinate Missing Depot',
            'type'         => 'warehouse',
            'city'         => 'Vilnius',
            'country_code' => 'LT',
            'latitude'     => null,
            'longitude'    => null,
        ]);

        $response = $this->getJson(route('locations.api.index'));

        $response
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('total', 0)
                ->has('locations', 0)
            );
    }

    public function test_locations_statistics_counts_enabled_and_disabled_locations(): void
    {
        // Seed a mixture of enabled and disabled locations to verify aggregate accuracy.
        Location::factory()->create([
            'is_enabled'   => true,
            'is_default'   => true,
            'type'         => 'warehouse',
            'country_code' => 'LT',
        ]);
        Location::factory()->create([
            'is_enabled'   => true,
            'is_default'   => false,
            'type'         => 'store',
            'country_code' => 'LV',
        ]);
        Location::factory()->create([
            'is_enabled'   => false,
            'is_default'   => false,
            'type'         => 'warehouse',
            'country_code' => 'LT',
        ]);

        $response = $this->getJson(route('locations.api.statistics'));

        $response
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('total_locations', 3)
                ->where('enabled_locations', 2)
                ->where('disabled_locations', 1)
                ->where('default_locations', 1)
                ->has('by_type', fn (AssertableJson $types) => $types
                    ->where('Warehouse', 2)
                    ->where('Store', 1)
                )
                ->has('by_country', fn (AssertableJson $countries) => $countries
                    ->where('LT', 2)
                    ->where('LV', 1)
                )
            );
    }

    public function test_locations_nearby_requires_coordinates(): void
    {
        // Calling the endpoint without coordinates should trigger the validation guardrail.
        $response = $this->getJson(route('locations.api.nearby'));

        $response
            ->assertStatus(400)
            ->assertJson(fn (AssertableJson $json) => $json->where('error', 'Latitude and longitude are required'));
    }
}
