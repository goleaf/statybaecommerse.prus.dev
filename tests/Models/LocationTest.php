<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Country;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a supporting country instance so the location factory remains valid.
     */
    private function setUpCountry(): Country
    {
        // We return the persisted model because factories expect an existing country code reference.
        return Country::factory()->create([
            'cca2' => 'LT',
            'name' => 'Lithuania',
        ]);
    }

    public function test_it_resolves_core_relationships(): void
    {
        $this->setUpCountry();

        // Persist a location record with a matching country code reference.
        $location = Location::factory()->create([
            'country_code' => 'LT',
        ]);

        // The location should have an associated country model eager loaded on demand.
        $this->assertInstanceOf(Country::class, $location->country);
        $this->assertSame('LT', $location->country->cca2);

        // Translation relations are provided by the HasTranslations trait.
        $this->assertInstanceOf(Collection::class, $location->translations);
    }

    public function test_it_orders_locations_by_name_consistently(): void
    {
        $this->setUpCountry();

        // Create locations with intentionally mixed casing to exercise the scope ordering.
        $alpha = Location::factory()->create(['name' => 'alpha', 'country_code' => 'LT']);
        $zulu = Location::factory()->create(['name' => 'Zulu', 'country_code' => 'LT']);
        $bravo = Location::factory()->create(['name' => 'bravo', 'country_code' => 'LT']);

        // Fetch the ordered collection using the dedicated scope.
        $ordered = Location::orderedByName()->pluck('id')->all();

        $this->assertSame([
            $alpha->id,
            $bravo->id,
            $zulu->id,
        ], $ordered);
    }

    public function test_it_filters_by_status_flags(): void
    {
        $this->setUpCountry();

        // Create a mix of enabled/default combinations to exercise the query scopes.
        $enabled = Location::factory()->create(['is_enabled' => true, 'is_default' => false, 'country_code' => 'LT']);
        $default = Location::factory()->create(['is_enabled' => false, 'is_default' => true, 'country_code' => 'LT']);
        $neither = Location::factory()->create(['is_enabled' => false, 'is_default' => false, 'country_code' => 'LT']);

        // Enabled scope should only return the enabled row.
        $this->assertTrue(Location::enabled()->pluck('id')->contains($enabled->id));
        $this->assertFalse(Location::enabled()->pluck('id')->contains($neither->id));

        // Default scope should only return the default record.
        $this->assertTrue(Location::default()->pluck('id')->contains($default->id));
        $this->assertFalse(Location::default()->pluck('id')->contains($neither->id));
    }

    public function test_it_exposes_readable_accessors(): void
    {
        $this->setUpCountry();

        // Compose a realistic address and business configuration payload.
        $location = Location::factory()->create([
            'name' => 'Vilnius Warehouse',
            'address_line_1' => 'Vilniaus g. 1',
            'address_line_2' => 'Suite 5',
            'city' => 'Vilnius',
            'state' => 'Vilniaus apskritis',
            'postal_code' => '01103',
            'type' => 'warehouse',
            'latitude' => 54.6872,
            'longitude' => 25.2797,
            'opening_hours' => [
                [
                    'day' => 'monday',
                    'open_time' => '09:00',
                    'close_time' => '17:00',
                    'is_closed' => false,
                ],
            ],
            'country_code' => 'LT',
        ]);

        // The formatted address accessor should concatenate all available parts.
        $this->assertSame(
            'Vilniaus g. 1, Suite 5, Vilnius, Vilniaus apskritis, 01103',
            $location->full_address,
        );

        // Coordinate helpers should expose both the string and URL representation.
        $this->assertSame('54.6872, 25.2797', $location->coordinates);
        $this->assertSame('https://www.google.com/maps?q=54.6872,25.2797', $location->google_maps_url);

        // Opening hours helpers should acknowledge availability and current state.
        $this->assertTrue($location->hasOpeningHours());
        $this->assertIsArray($location->getFormattedOpeningHours());
    }
}
