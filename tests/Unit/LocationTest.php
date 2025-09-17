<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Country;
use App\Models\Location;
use App\Models\Translations\LocationTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocationTest extends TestCase
{
    use RefreshDatabase;

    private function createTestCountry(): Country
    {
        return Country::factory()->create([
            'cca2' => 'LT',
            'name' => 'Lithuania',
        ]);
    }

    public function test_location_can_be_created(): void
    {
        $country = $this->createTestCountry();

        $location = Location::factory()->create([
            'name' => 'Test Location',
            'code' => 'TEST001',
            'type' => 'warehouse',
            'is_enabled' => true,
            'latitude' => 54.6872,
            'longitude' => 25.2797,
            'country_code' => 'LT',
        ]);

        $this->assertDatabaseHas('locations', [
            'name' => 'Test Location',
            'code' => 'TEST001',
            'type' => 'warehouse',
            'is_enabled' => true,
            'latitude' => 54.6872,
            'longitude' => 25.2797,
            'country_code' => 'LT',
        ]);

        $this->assertEquals('Test Location', $location->name);
        $this->assertEquals('TEST001', $location->code);
        $this->assertEquals('warehouse', $location->type);
        $this->assertTrue($location->is_enabled);
        $this->assertEquals(54.6872, $location->latitude);
        $this->assertEquals(25.2797, $location->longitude);
    }

    public function test_location_translation_methods(): void
    {
        $country = $this->createTestCountry();
        
        $location = Location::factory()->create([
            'name' => 'Original Name',
            'country_code' => 'LT',
        ]);
        
        // Create translation
        LocationTranslation::factory()->create([
            'location_id' => $location->id,
            'locale' => 'en',
            'name' => 'English Name',
            'slug' => 'english-slug',
            'description' => 'English Description',
        ]);

        // Test translated methods
        $this->assertEquals('English Name', $location->getTranslatedName('en'));
        $this->assertEquals('english-slug', $location->getTranslatedSlug('en'));
        $this->assertEquals('English Description', $location->getTranslatedDescription('en'));

        // Test fallback to original
        $this->assertEquals('Original Name', $location->getTranslatedName('lt'));
        $this->assertEquals('Original Name', $location->getTranslatedName());
    }

    public function test_location_scopes(): void
    {
        $country = $this->createTestCountry();

        // Create locations with different attributes
        $enabledLocation = Location::factory()->create([
            'is_enabled' => true,
            'country_code' => 'LT',
        ]);
        
        $disabledLocation = Location::factory()->create([
            'is_enabled' => false,
            'country_code' => 'LT',
        ]);

        $defaultLocation = Location::factory()->create([
            'is_default' => true,
            'country_code' => 'LT',
        ]);

        $warehouseLocation = Location::factory()->create([
            'type' => 'warehouse',
            'country_code' => 'LT',
        ]);

        // Test scopes
        $enabledLocations = Location::enabled()->get();
        $this->assertTrue($enabledLocations->contains('id', $enabledLocation->id));
        $this->assertFalse($enabledLocations->contains('id', $disabledLocation->id));

        $defaultLocations = Location::default()->get();
        $this->assertTrue($defaultLocations->contains('id', $defaultLocation->id));

        $warehouses = Location::byType('warehouse')->get();
        $this->assertTrue($warehouses->contains('id', $warehouseLocation->id));
    }

    public function test_location_type_methods(): void
    {
        $country = $this->createTestCountry();

        $warehouse = Location::factory()->create([
            'type' => 'warehouse',
            'country_code' => 'LT',
        ]);

        $store = Location::factory()->create([
            'type' => 'store',
            'country_code' => 'LT',
        ]);

        $this->assertTrue($warehouse->isWarehouse());
        $this->assertFalse($warehouse->isStore());

        $this->assertTrue($store->isStore());
        $this->assertFalse($store->isWarehouse());
    }

    public function test_location_coordinate_methods(): void
    {
        $country = $this->createTestCountry();

        $location = Location::factory()->create([
            'latitude' => 54.6872,
            'longitude' => 25.2797,
            'country_code' => 'LT',
        ]);

        $this->assertTrue($location->hasCoordinates());
        $this->assertEquals('54.6872, 25.2797', $location->getCoordinatesAttribute());

        $locationWithoutCoords = Location::factory()->create([
            'latitude' => null,
            'longitude' => null,
            'country_code' => 'LT',
        ]);

        $this->assertFalse($locationWithoutCoords->hasCoordinates());
        $this->assertNull($locationWithoutCoords->getCoordinatesAttribute());
    }

    public function test_location_opening_hours_methods(): void
    {
        $country = $this->createTestCountry();

        $openingHours = [
            [
                'day' => 'monday',
                'open_time' => '09:00',
                'close_time' => '17:00',
                'is_closed' => false,
            ],
            [
                'day' => 'tuesday',
                'open_time' => '09:00',
                'close_time' => '17:00',
                'is_closed' => false,
            ],
        ];

        $location = Location::factory()->create([
            'opening_hours' => $openingHours,
            'country_code' => 'LT',
        ]);

        $this->assertTrue($location->hasOpeningHours());
        $this->assertEquals($openingHours, $location->opening_hours);

        $locationWithoutHours = Location::factory()->create([
            'opening_hours' => null,
            'country_code' => 'LT',
        ]);

        $this->assertFalse($locationWithoutHours->hasOpeningHours());
        $this->assertNull($locationWithoutHours->opening_hours);
    }

    public function test_location_address_methods(): void
    {
        $country = $this->createTestCountry();

        $location = Location::factory()->create([
            'address_line_1' => 'Vilniaus g. 1',
            'address_line_2' => 'Apt. 5',
            'city' => 'Vilnius',
            'state' => 'Vilniaus apskritis',
            'postal_code' => '01103',
            'country_code' => 'LT',
        ]);

        $this->assertEquals('Vilniaus g. 1, Apt. 5, Vilnius, Vilniaus apskritis, 01103', $location->getFullAddressAttribute());

        $locationWithoutAddress = Location::factory()->create([
            'address_line_1' => null,
            'address_line_2' => null,
            'city' => null,
            'state' => null,
            'postal_code' => null,
            'country_code' => 'LT',
        ]);

        $this->assertEquals('', $locationWithoutAddress->getFullAddressAttribute());
    }

    public function test_location_translation_management(): void
    {
        $country = $this->createTestCountry();
        
        $location = Location::factory()->create([
            'name' => 'Original Name',
            'country_code' => 'LT',
        ]);

        // Test available locales
        $this->assertEquals([], $location->getAvailableLocales());

        // Test hasTranslationFor
        $this->assertFalse($location->hasTranslationFor('en'));

        // Test getOrCreateTranslation
        $translation = $location->getOrCreateTranslation('en');
        $this->assertInstanceOf(LocationTranslation::class, $translation);
        $this->assertEquals('en', $translation->locale);

        // Test updateTranslation
        $location->updateTranslation('en', [
            'name' => 'English Name',
            'description' => 'English Description',
        ]);

        $translation = $location->translations()->where('locale', 'en')->first();
        $this->assertEquals('English Name', $translation->name);
        $this->assertEquals('English Description', $translation->description);

        // Test updateTranslations
        $location->updateTranslations([
            'lt' => [
                'name' => 'Lietuviškas pavadinimas',
                'description' => 'Lietuviškas aprašymas',
            ],
        ]);

        $ltTranslation = $location->translations()->where('locale', 'lt')->first();
        $this->assertEquals('Lietuviškas pavadinimas', $ltTranslation->name);
        $this->assertEquals('Lietuviškas aprašymas', $ltTranslation->description);
    }

    public function test_location_helper_methods(): void
    {
        $country = $this->createTestCountry();
        
        $location = Location::factory()->create([
            'name' => 'Test Location',
            'type' => 'warehouse',
            'country_code' => 'LT',
        ]);

        // Test getFullDisplayName
        $this->assertEquals('Test Location, Lithuania', $location->getFullDisplayName());

        // Test getLocationInfo
        $locationInfo = $location->getLocationInfo();
        $this->assertArrayHasKey('basic', $locationInfo);
        $this->assertArrayHasKey('address', $locationInfo);
        $this->assertArrayHasKey('contact', $locationInfo);
        $this->assertArrayHasKey('coordinates', $locationInfo);
        $this->assertArrayHasKey('business', $locationInfo);
        $this->assertArrayHasKey('status', $locationInfo);

        // Test getBusinessInfo
        $businessInfo = $location->getBusinessInfo();
        $this->assertArrayHasKey('type', $businessInfo);
        $this->assertArrayHasKey('type_label', $businessInfo);
        $this->assertArrayHasKey('is_warehouse', $businessInfo);
        $this->assertArrayHasKey('is_store', $businessInfo);

        // Test getCompleteInfo
        $completeInfo = $location->getCompleteInfo();
        $this->assertArrayHasKey('basic', $completeInfo);
        $this->assertArrayHasKey('location', $completeInfo);
        $this->assertArrayHasKey('business', $completeInfo);
        $this->assertArrayHasKey('status', $completeInfo);
    }

    public function test_location_relations(): void
    {
        $country = $this->createTestCountry();
        
        $location = Location::factory()->create([
            'country_code' => 'LT',
        ]);

        // Test country relation
        $this->assertInstanceOf(Country::class, $location->country);
        $this->assertEquals('LT', $location->country->cca2);

        // Test translations relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $location->translations);
    }

    public function test_location_search_scope(): void
    {
        $country = $this->createTestCountry();

        $location1 = Location::factory()->create([
            'name' => 'Vilnius Warehouse',
            'code' => 'VIL001',
            'country_code' => 'LT',
        ]);

        $location2 = Location::factory()->create([
            'name' => 'Kaunas Store',
            'code' => 'KAU002',
            'country_code' => 'LT',
        ]);

        // Test search by name using where clause
        $results = Location::where('name', 'like', '%Vilnius%')->get();
        $this->assertTrue($results->contains('id', $location1->id));
        $this->assertFalse($results->contains('id', $location2->id));

        // Test search by code
        $results = Location::where('code', 'VIL001')->get();
        $this->assertTrue($results->contains('id', $location1->id));
    }
}