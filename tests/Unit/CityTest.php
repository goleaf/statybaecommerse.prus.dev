<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_can_be_created(): void
    {
        $country = Country::factory()->create();
        $region = Region::factory()->create(['country_id' => $country->id]);
        $zone = Zone::factory()->create();
        
        $city = City::factory()->create([
            'name' => 'Vilnius',
            'code' => 'VIL',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'zone_id' => $zone->id,
            'is_active' => true,
            'is_capital' => true,
        ]);

        $this->assertDatabaseHas('cities', [
            'name' => 'Vilnius',
            'code' => 'VIL',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'zone_id' => $zone->id,
            'is_active' => true,
            'is_capital' => true,
        ]);
    }

    public function test_city_belongs_to_country(): void
    {
        $country = Country::factory()->create();
        $city = City::factory()->create(['country_id' => $country->id]);

        $this->assertInstanceOf(Country::class, $city->country);
        $this->assertEquals($country->id, $city->country->id);
    }

    public function test_city_belongs_to_region(): void
    {
        $region = Region::factory()->create();
        $city = City::factory()->create(['region_id' => $region->id]);

        $this->assertInstanceOf(Region::class, $city->region);
        $this->assertEquals($region->id, $city->region->id);
    }

    public function test_city_belongs_to_zone(): void
    {
        $zone = Zone::factory()->create();
        $city = City::factory()->create(['zone_id' => $zone->id]);

        $this->assertInstanceOf(Zone::class, $city->zone);
        $this->assertEquals($zone->id, $city->zone->id);
    }

    public function test_city_casts_work_correctly(): void
    {
        $city = City::factory()->create([
            'is_active' => true,
            'is_capital' => false,
            'sort_order' => 5,
            'created_at' => now(),
        ]);

        $this->assertIsBool($city->is_active);
        $this->assertIsBool($city->is_capital);
        $this->assertIsInt($city->sort_order);
        $this->assertInstanceOf(\Carbon\Carbon::class, $city->created_at);
    }

    public function test_city_fillable_attributes(): void
    {
        $city = new City();
        $fillable = $city->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('code', $fillable);
        $this->assertContains('country_id', $fillable);
        $this->assertContains('region_id', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_city_scope_active(): void
    {
        $activeCity = City::factory()->create(['is_active' => true]);
        $inactiveCity = City::factory()->create(['is_active' => false]);

        $activeCities = City::active()->get();

        $this->assertTrue($activeCities->contains($activeCity));
        $this->assertFalse($activeCities->contains($inactiveCity));
    }

    public function test_city_scope_capital(): void
    {
        $capitalCity = City::factory()->create(['is_capital' => true]);
        $nonCapitalCity = City::factory()->create(['is_capital' => false]);

        $capitalCities = City::capital()->get();

        $this->assertTrue($capitalCities->contains($capitalCity));
        $this->assertFalse($capitalCities->contains($nonCapitalCity));
    }

    public function test_city_scope_ordered(): void
    {
        $city1 = City::factory()->create(['sort_order' => 2]);
        $city2 = City::factory()->create(['sort_order' => 1]);
        $city3 = City::factory()->create(['sort_order' => 3]);

        $orderedCities = City::ordered()->get();

        $this->assertEquals($city2->id, $orderedCities->first()->id);
        $this->assertEquals($city3->id, $orderedCities->last()->id);
    }

    public function test_city_can_have_description(): void
    {
        $city = City::factory()->create([
            'description' => 'Vilnius is the capital and largest city of Lithuania',
        ]);

        $this->assertEquals('Vilnius is the capital and largest city of Lithuania', $city->description);
    }

    public function test_city_can_have_type(): void
    {
        $city = City::factory()->create([
            'type' => 'capital',
        ]);

        $this->assertEquals('capital', $city->type);
    }

    public function test_city_can_have_population(): void
    {
        $city = City::factory()->create([
            'population' => 700000,
        ]);

        $this->assertEquals(700000, $city->population);
    }

    public function test_city_can_have_area(): void
    {
        $city = City::factory()->create([
            'area' => 401.0,
        ]);

        $this->assertEquals(401.0, $city->area);
    }

    public function test_city_can_have_density(): void
    {
        $city = City::factory()->create([
            'density' => 1745.6,
        ]);

        $this->assertEquals(1745.6, $city->density);
    }

    public function test_city_can_have_elevation(): void
    {
        $city = City::factory()->create([
            'elevation' => 112.0,
        ]);

        $this->assertEquals(112.0, $city->elevation);
    }

    public function test_city_can_have_timezone(): void
    {
        $city = City::factory()->create([
            'timezone' => 'Europe/Vilnius',
        ]);

        $this->assertEquals('Europe/Vilnius', $city->timezone);
    }

    public function test_city_can_have_currency(): void
    {
        $city = City::factory()->create([
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
        ]);

        $this->assertEquals('EUR', $city->currency_code);
        $this->assertEquals('€', $city->currency_symbol);
    }

    public function test_city_can_have_language(): void
    {
        $city = City::factory()->create([
            'language_code' => 'lt',
            'language_name' => 'Lithuanian',
        ]);

        $this->assertEquals('lt', $city->language_code);
        $this->assertEquals('Lithuanian', $city->language_name);
    }

    public function test_city_can_have_phone_code(): void
    {
        $city = City::factory()->create([
            'phone_code' => '+370',
        ]);

        $this->assertEquals('+370', $city->phone_code);
    }

    public function test_city_can_have_postal_code(): void
    {
        $city = City::factory()->create([
            'postal_code' => 'LT-01001',
        ]);

        $this->assertEquals('LT-01001', $city->postal_code);
    }

    public function test_city_can_have_latitude(): void
    {
        $city = City::factory()->create([
            'latitude' => 54.6872,
        ]);

        $this->assertEquals(54.6872, $city->latitude);
    }

    public function test_city_can_have_longitude(): void
    {
        $city = City::factory()->create([
            'longitude' => 25.2797,
        ]);

        $this->assertEquals(25.2797, $city->longitude);
    }

    public function test_city_can_have_metadata(): void
    {
        $city = City::factory()->create([
            'metadata' => [
                'created_by' => 'admin',
                'version' => '1.0',
                'tags' => ['vilnius', 'capital', 'lithuania'],
            ],
        ]);

        $this->assertIsArray($city->metadata);
        $this->assertEquals('admin', $city->metadata['created_by']);
        $this->assertEquals('1.0', $city->metadata['version']);
        $this->assertIsArray($city->metadata['tags']);
    }

    public function test_city_can_have_scope_by_code(): void
    {
        $city1 = City::factory()->create(['code' => 'VIL']);
        $city2 = City::factory()->create(['code' => 'KAU']);

        $vilCities = City::byCode('VIL')->get();

        $this->assertTrue($vilCities->contains($city1));
        $this->assertFalse($vilCities->contains($city2));
    }

    public function test_city_can_have_scope_by_country(): void
    {
        $country1 = Country::factory()->create();
        $country2 = Country::factory()->create();
        
        $city1 = City::factory()->create(['country_id' => $country1->id]);
        $city2 = City::factory()->create(['country_id' => $country2->id]);

        $country1Cities = City::byCountry($country1->id)->get();

        $this->assertTrue($country1Cities->contains($city1));
        $this->assertFalse($country1Cities->contains($city2));
    }

    public function test_city_can_have_scope_by_region(): void
    {
        $region1 = Region::factory()->create();
        $region2 = Region::factory()->create();
        
        $city1 = City::factory()->create(['region_id' => $region1->id]);
        $city2 = City::factory()->create(['region_id' => $region2->id]);

        $region1Cities = City::byRegion($region1->id)->get();

        $this->assertTrue($region1Cities->contains($city1));
        $this->assertFalse($region1Cities->contains($city2));
    }

    public function test_city_can_have_scope_by_zone(): void
    {
        $zone1 = Zone::factory()->create();
        $zone2 = Zone::factory()->create();
        
        $city1 = City::factory()->create(['zone_id' => $zone1->id]);
        $city2 = City::factory()->create(['zone_id' => $zone2->id]);

        $zone1Cities = City::byZone($zone1->id)->get();

        $this->assertTrue($zone1Cities->contains($city1));
        $this->assertFalse($zone1Cities->contains($city2));
    }

    public function test_city_can_have_scope_by_type(): void
    {
        $city1 = City::factory()->create(['type' => 'capital']);
        $city2 = City::factory()->create(['type' => 'town']);

        $capitalCities = City::byType('capital')->get();

        $this->assertTrue($capitalCities->contains($city1));
        $this->assertFalse($capitalCities->contains($city2));
    }

    public function test_city_can_have_scope_by_population(): void
    {
        $city1 = City::factory()->create(['population' => 1000000]);
        $city2 = City::factory()->create(['population' => 500000]);

        $largeCities = City::byPopulation(750000)->get();

        $this->assertTrue($largeCities->contains($city1));
        $this->assertFalse($largeCities->contains($city2));
    }

    public function test_city_can_have_scope_by_area(): void
    {
        $city1 = City::factory()->create(['area' => 500.0]);
        $city2 = City::factory()->create(['area' => 200.0]);

        $largeCities = City::byArea(350.0)->get();

        $this->assertTrue($largeCities->contains($city1));
        $this->assertFalse($largeCities->contains($city2));
    }

    public function test_city_can_have_scope_by_density(): void
    {
        $city1 = City::factory()->create(['density' => 2000.0]);
        $city2 = City::factory()->create(['density' => 1000.0]);

        $denseCities = City::byDensity(1500.0)->get();

        $this->assertTrue($denseCities->contains($city1));
        $this->assertFalse($denseCities->contains($city2));
    }

    public function test_city_can_have_scope_by_elevation(): void
    {
        $city1 = City::factory()->create(['elevation' => 200.0]);
        $city2 = City::factory()->create(['elevation' => 100.0]);

        $highCities = City::byElevation(150.0)->get();

        $this->assertTrue($highCities->contains($city1));
        $this->assertFalse($highCities->contains($city2));
    }

    public function test_city_can_have_scope_by_timezone(): void
    {
        $city1 = City::factory()->create(['timezone' => 'Europe/Vilnius']);
        $city2 = City::factory()->create(['timezone' => 'Europe/London']);

        $vilniusCities = City::byTimezone('Europe/Vilnius')->get();

        $this->assertTrue($vilniusCities->contains($city1));
        $this->assertFalse($vilniusCities->contains($city2));
    }

    public function test_city_can_have_scope_by_currency(): void
    {
        $city1 = City::factory()->create(['currency_code' => 'EUR']);
        $city2 = City::factory()->create(['currency_code' => 'USD']);

        $eurCities = City::byCurrency('EUR')->get();

        $this->assertTrue($eurCities->contains($city1));
        $this->assertFalse($eurCities->contains($city2));
    }

    public function test_city_can_have_scope_by_language(): void
    {
        $city1 = City::factory()->create(['language_code' => 'lt']);
        $city2 = City::factory()->create(['language_code' => 'en']);

        $ltCities = City::byLanguage('lt')->get();

        $this->assertTrue($ltCities->contains($city1));
        $this->assertFalse($ltCities->contains($city2));
    }

    public function test_city_can_have_scope_by_phone_code(): void
    {
        $city1 = City::factory()->create(['phone_code' => '+370']);
        $city2 = City::factory()->create(['phone_code' => '+371']);

        $ltPhoneCities = City::byPhoneCode('+370')->get();

        $this->assertTrue($ltPhoneCities->contains($city1));
        $this->assertFalse($ltPhoneCities->contains($city2));
    }

    public function test_city_can_have_scope_by_postal_code(): void
    {
        $city1 = City::factory()->create(['postal_code' => 'LT-01001']);
        $city2 = City::factory()->create(['postal_code' => 'LT-02001']);

        $vilniusCities = City::byPostalCode('LT-01001')->get();

        $this->assertTrue($vilniusCities->contains($city1));
        $this->assertFalse($vilniusCities->contains($city2));
    }

    public function test_city_can_have_scope_by_latitude(): void
    {
        $city1 = City::factory()->create(['latitude' => 54.6872]);
        $city2 = City::factory()->create(['latitude' => 55.6872]);

        $vilniusCities = City::byLatitude(54.6872)->get();

        $this->assertTrue($vilniusCities->contains($city1));
        $this->assertFalse($vilniusCities->contains($city2));
    }

    public function test_city_can_have_scope_by_longitude(): void
    {
        $city1 = City::factory()->create(['longitude' => 25.2797]);
        $city2 = City::factory()->create(['longitude' => 26.2797]);

        $vilniusCities = City::byLongitude(25.2797)->get();

        $this->assertTrue($vilniusCities->contains($city1));
        $this->assertFalse($vilniusCities->contains($city2));
    }

    public function test_city_can_have_scope_by_coordinates(): void
    {
        $city1 = City::factory()->create(['latitude' => 54.6872, 'longitude' => 25.2797]);
        $city2 = City::factory()->create(['latitude' => 55.6872, 'longitude' => 26.2797]);

        $vilniusCities = City::byCoordinates(54.6872, 25.2797)->get();

        $this->assertTrue($vilniusCities->contains($city1));
        $this->assertFalse($vilniusCities->contains($city2));
    }

    public function test_city_can_have_scope_by_country_code(): void
    {
        $country1 = Country::factory()->create(['code' => 'LT']);
        $country2 = Country::factory()->create(['code' => 'LV']);
        
        $city1 = City::factory()->create(['country_id' => $country1->id]);
        $city2 = City::factory()->create(['country_id' => $country2->id]);

        $ltCities = City::byCountryCode('LT')->get();

        $this->assertTrue($ltCities->contains($city1));
        $this->assertFalse($ltCities->contains($city2));
    }

    public function test_city_can_have_scope_by_country_iso_code(): void
    {
        $country1 = Country::factory()->create(['iso_code' => 'LTU']);
        $country2 = Country::factory()->create(['iso_code' => 'LVA']);
        
        $city1 = City::factory()->create(['country_id' => $country1->id]);
        $city2 = City::factory()->create(['country_id' => $country2->id]);

        $ltuCities = City::byCountryIsoCode('LTU')->get();

        $this->assertTrue($ltuCities->contains($city1));
        $this->assertFalse($ltuCities->contains($city2));
    }

    public function test_city_can_have_scope_by_country_continent(): void
    {
        $country1 = Country::factory()->create(['continent' => 'Europe']);
        $country2 = Country::factory()->create(['continent' => 'Asia']);
        
        $city1 = City::factory()->create(['country_id' => $country1->id]);
        $city2 = City::factory()->create(['country_id' => $country2->id]);

        $europeanCities = City::byCountryContinent('Europe')->get();

        $this->assertTrue($europeanCities->contains($city1));
        $this->assertFalse($europeanCities->contains($city2));
    }

    public function test_city_can_have_scope_by_country_currency(): void
    {
        $country1 = Country::factory()->create(['currency_code' => 'EUR']);
        $country2 = Country::factory()->create(['currency_code' => 'USD']);
        
        $city1 = City::factory()->create(['country_id' => $country1->id]);
        $city2 = City::factory()->create(['country_id' => $country2->id]);

        $eurCities = City::byCountryCurrency('EUR')->get();

        $this->assertTrue($eurCities->contains($city1));
        $this->assertFalse($eurCities->contains($city2));
    }

    public function test_city_can_have_scope_by_country_language(): void
    {
        $country1 = Country::factory()->create(['language_code' => 'lt']);
        $country2 = Country::factory()->create(['language_code' => 'en']);
        
        $city1 = City::factory()->create(['country_id' => $country1->id]);
        $city2 = City::factory()->create(['country_id' => $country2->id]);

        $ltCities = City::byCountryLanguage('lt')->get();

        $this->assertTrue($ltCities->contains($city1));
        $this->assertFalse($ltCities->contains($city2));
    }

    public function test_city_can_have_scope_by_country_timezone(): void
    {
        $country1 = Country::factory()->create(['timezone' => 'Europe/Vilnius']);
        $country2 = Country::factory()->create(['timezone' => 'Europe/London']);
        
        $city1 = City::factory()->create(['country_id' => $country1->id]);
        $city2 = City::factory()->create(['country_id' => $country2->id]);

        $vilniusCities = City::byCountryTimezone('Europe/Vilnius')->get();

        $this->assertTrue($vilniusCities->contains($city1));
        $this->assertFalse($vilniusCities->contains($city2));
    }

    public function test_city_can_have_scope_by_country_phone_code(): void
    {
        $country1 = Country::factory()->create(['phone_code' => '+370']);
        $country2 = Country::factory()->create(['phone_code' => '+371']);
        
        $city1 = City::factory()->create(['country_id' => $country1->id]);
        $city2 = City::factory()->create(['country_id' => $country2->id]);

        $ltPhoneCities = City::byCountryPhoneCode('+370')->get();

        $this->assertTrue($ltPhoneCities->contains($city1));
        $this->assertFalse($ltPhoneCities->contains($city2));
    }

    public function test_city_can_have_scope_by_country_capital(): void
    {
        $country1 = Country::factory()->create(['capital' => 'Vilnius']);
        $country2 = Country::factory()->create(['capital' => 'Riga']);
        
        $city1 = City::factory()->create(['country_id' => $country1->id]);
        $city2 = City::factory()->create(['country_id' => $country2->id]);

        $vilniusCities = City::byCountryCapital('Vilnius')->get();

        $this->assertTrue($vilniusCities->contains($city1));
        $this->assertFalse($vilniusCities->contains($city2));
    }

    public function test_city_can_have_scope_by_region_code(): void
    {
        $region1 = Region::factory()->create(['code' => 'VL']);
        $region2 = Region::factory()->create(['code' => 'KL']);
        
        $city1 = City::factory()->create(['region_id' => $region1->id]);
        $city2 = City::factory()->create(['region_id' => $region2->id]);

        $vlCities = City::byRegionCode('VL')->get();

        $this->assertTrue($vlCities->contains($city1));
        $this->assertFalse($vlCities->contains($city2));
    }

    public function test_city_can_have_scope_by_region_type(): void
    {
        $region1 = Region::factory()->create(['type' => 'county']);
        $region2 = Region::factory()->create(['type' => 'state']);
        
        $city1 = City::factory()->create(['region_id' => $region1->id]);
        $city2 = City::factory()->create(['region_id' => $region2->id]);

        $countyCities = City::byRegionType('county')->get();

        $this->assertTrue($countyCities->contains($city1));
        $this->assertFalse($countyCities->contains($city2));
    }

    public function test_city_can_have_scope_by_region_capital(): void
    {
        $region1 = Region::factory()->create(['capital' => 'Vilnius']);
        $region2 = Region::factory()->create(['capital' => 'Kaunas']);
        
        $city1 = City::factory()->create(['region_id' => $region1->id]);
        $city2 = City::factory()->create(['region_id' => $region2->id]);

        $vilniusCities = City::byRegionCapital('Vilnius')->get();

        $this->assertTrue($vilniusCities->contains($city1));
        $this->assertFalse($vilniusCities->contains($city2));
    }

    public function test_city_can_have_scope_by_region_population(): void
    {
        $region1 = Region::factory()->create(['population' => 1000000]);
        $region2 = Region::factory()->create(['population' => 500000]);
        
        $city1 = City::factory()->create(['region_id' => $region1->id]);
        $city2 = City::factory()->create(['region_id' => $region2->id]);

        $largeRegionCities = City::byRegionPopulation(750000)->get();

        $this->assertTrue($largeRegionCities->contains($city1));
        $this->assertFalse($largeRegionCities->contains($city2));
    }

    public function test_city_can_have_scope_by_region_area(): void
    {
        $region1 = Region::factory()->create(['area' => 10000.0]);
        $region2 = Region::factory()->create(['area' => 5000.0]);
        
        $city1 = City::factory()->create(['region_id' => $region1->id]);
        $city2 = City::factory()->create(['region_id' => $region2->id]);

        $largeRegionCities = City::byRegionArea(7500.0)->get();

        $this->assertTrue($largeRegionCities->contains($city1));
        $this->assertFalse($largeRegionCities->contains($city2));
    }

    public function test_city_can_have_scope_by_region_density(): void
    {
        $region1 = Region::factory()->create(['density' => 100.0]);
        $region2 = Region::factory()->create(['density' => 50.0]);
        
        $city1 = City::factory()->create(['region_id' => $region1->id]);
        $city2 = City::factory()->create(['region_id' => $region2->id]);

        $denseRegionCities = City::byRegionDensity(75.0)->get();

        $this->assertTrue($denseRegionCities->contains($city1));
        $this->assertFalse($denseRegionCities->contains($city2));
    }

    public function test_city_can_have_scope_by_region_timezone(): void
    {
        $region1 = Region::factory()->create(['timezone' => 'Europe/Vilnius']);
        $region2 = Region::factory()->create(['timezone' => 'Europe/London']);
        
        $city1 = City::factory()->create(['region_id' => $region1->id]);
        $city2 = City::factory()->create(['region_id' => $region2->id]);

        $vilniusRegionCities = City::byRegionTimezone('Europe/Vilnius')->get();

        $this->assertTrue($vilniusRegionCities->contains($city1));
        $this->assertFalse($vilniusRegionCities->contains($city2));
    }

    public function test_city_can_have_scope_by_region_currency(): void
    {
        $region1 = Region::factory()->create(['currency_code' => 'EUR']);
        $region2 = Region::factory()->create(['currency_code' => 'USD']);
        
        $city1 = City::factory()->create(['region_id' => $region1->id]);
        $city2 = City::factory()->create(['region_id' => $region2->id]);

        $eurRegionCities = City::byRegionCurrency('EUR')->get();

        $this->assertTrue($eurRegionCities->contains($city1));
        $this->assertFalse($eurRegionCities->contains($city2));
    }

    public function test_city_can_have_scope_by_region_language(): void
    {
        $region1 = Region::factory()->create(['language_code' => 'lt']);
        $region2 = Region::factory()->create(['language_code' => 'en']);
        
        $city1 = City::factory()->create(['region_id' => $region1->id]);
        $city2 = City::factory()->create(['region_id' => $region2->id]);

        $ltRegionCities = City::byRegionLanguage('lt')->get();

        $this->assertTrue($ltRegionCities->contains($city1));
        $this->assertFalse($ltRegionCities->contains($city2));
    }

    public function test_city_can_have_scope_by_region_phone_code(): void
    {
        $region1 = Region::factory()->create(['phone_code' => '+370']);
        $region2 = Region::factory()->create(['phone_code' => '+371']);
        
        $city1 = City::factory()->create(['region_id' => $region1->id]);
        $city2 = City::factory()->create(['region_id' => $region2->id]);

        $ltPhoneRegionCities = City::byRegionPhoneCode('+370')->get();

        $this->assertTrue($ltPhoneRegionCities->contains($city1));
        $this->assertFalse($ltPhoneRegionCities->contains($city2));
    }

    public function test_city_can_have_scope_by_zone_code(): void
    {
        $zone1 = Zone::factory()->create(['code' => 'EU']);
        $zone2 = Zone::factory()->create(['code' => 'US']);
        
        $city1 = City::factory()->create(['zone_id' => $zone1->id]);
        $city2 = City::factory()->create(['zone_id' => $zone2->id]);

        $euCities = City::byZoneCode('EU')->get();

        $this->assertTrue($euCities->contains($city1));
        $this->assertFalse($euCities->contains($city2));
    }

    public function test_city_can_have_scope_by_zone_type(): void
    {
        $zone1 = Zone::factory()->create(['type' => 'european']);
        $zone2 = Zone::factory()->create(['type' => 'american']);
        
        $city1 = City::factory()->create(['zone_id' => $zone1->id]);
        $city2 = City::factory()->create(['zone_id' => $zone2->id]);

        $europeanCities = City::byZoneType('european')->get();

        $this->assertTrue($europeanCities->contains($city1));
        $this->assertFalse($europeanCities->contains($city2));
    }

    public function test_city_can_have_scope_by_zone_currency(): void
    {
        $zone1 = Zone::factory()->create(['currency_code' => 'EUR']);
        $zone2 = Zone::factory()->create(['currency_code' => 'USD']);
        
        $city1 = City::factory()->create(['zone_id' => $zone1->id]);
        $city2 = City::factory()->create(['zone_id' => $zone2->id]);

        $eurZoneCities = City::byZoneCurrency('EUR')->get();

        $this->assertTrue($eurZoneCities->contains($city1));
        $this->assertFalse($eurZoneCities->contains($city2));
    }

    public function test_city_can_have_scope_by_zone_language(): void
    {
        $zone1 = Zone::factory()->create(['default_language' => 'lt']);
        $zone2 = Zone::factory()->create(['default_language' => 'en']);
        
        $city1 = City::factory()->create(['zone_id' => $zone1->id]);
        $city2 = City::factory()->create(['zone_id' => $zone2->id]);

        $ltZoneCities = City::byZoneLanguage('lt')->get();

        $this->assertTrue($ltZoneCities->contains($city1));
        $this->assertFalse($ltZoneCities->contains($city2));
    }

    public function test_city_can_have_scope_by_zone_timezone(): void
    {
        $zone1 = Zone::factory()->create(['timezone' => 'Europe/Vilnius']);
        $zone2 = Zone::factory()->create(['timezone' => 'Europe/London']);
        
        $city1 = City::factory()->create(['zone_id' => $zone1->id]);
        $city2 = City::factory()->create(['zone_id' => $zone2->id]);

        $vilniusZoneCities = City::byZoneTimezone('Europe/Vilnius')->get();

        $this->assertTrue($vilniusZoneCities->contains($city1));
        $this->assertFalse($vilniusZoneCities->contains($city2));
    }

    public function test_city_can_have_scope_by_zone_phone_code(): void
    {
        $zone1 = Zone::factory()->create(['phone_code' => '+370']);
        $zone2 = Zone::factory()->create(['phone_code' => '+371']);
        
        $city1 = City::factory()->create(['zone_id' => $zone1->id]);
        $city2 = City::factory()->create(['zone_id' => $zone2->id]);

        $ltPhoneZoneCities = City::byZonePhoneCode('+370')->get();

        $this->assertTrue($ltPhoneZoneCities->contains($city1));
        $this->assertFalse($ltPhoneZoneCities->contains($city2));
    }

    public function test_city_can_have_scope_by_zone_currency_and_language(): void
    {
        $zone1 = Zone::factory()->create(['currency_code' => 'EUR', 'default_language' => 'lt']);
        $zone2 = Zone::factory()->create(['currency_code' => 'USD', 'default_language' => 'lt']);
        
        $city1 = City::factory()->create(['zone_id' => $zone1->id]);
        $city2 = City::factory()->create(['zone_id' => $zone2->id]);

        $eurLtZoneCities = City::byZoneCurrencyAndLanguage('EUR', 'lt')->get();

        $this->assertTrue($eurLtZoneCities->contains($city1));
        $this->assertFalse($eurLtZoneCities->contains($city2));
    }

    public function test_city_can_have_scope_by_zone_currency_and_timezone(): void
    {
        $zone1 = Zone::factory()->create(['currency_code' => 'EUR', 'timezone' => 'Europe/Vilnius']);
        $zone2 = Zone::factory()->create(['currency_code' => 'USD', 'timezone' => 'Europe/Vilnius']);
        
        $city1 = City::factory()->create(['zone_id' => $zone1->id]);
        $city2 = City::factory()->create(['zone_id' => $zone2->id]);

        $eurVilniusZoneCities = City::byZoneCurrencyAndTimezone('EUR', 'Europe/Vilnius')->get();

        $this->assertTrue($eurVilniusZoneCities->contains($city1));
        $this->assertFalse($eurVilniusZoneCities->contains($city2));
    }

    public function test_city_can_have_scope_by_zone_language_and_timezone(): void
    {
        $zone1 = Zone::factory()->create(['default_language' => 'lt', 'timezone' => 'Europe/Vilnius']);
        $zone2 = Zone::factory()->create(['default_language' => 'en', 'timezone' => 'Europe/Vilnius']);
        
        $city1 = City::factory()->create(['zone_id' => $zone1->id]);
        $city2 = City::factory()->create(['zone_id' => $zone2->id]);

        $ltVilniusZoneCities = City::byZoneLanguageAndTimezone('lt', 'Europe/Vilnius')->get();

        $this->assertTrue($ltVilniusZoneCities->contains($city1));
        $this->assertFalse($ltVilniusZoneCities->contains($city2));
    }

    public function test_city_can_have_scope_by_zone_currency_language_and_timezone(): void
    {
        $zone1 = Zone::factory()->create([
            'currency_code' => 'EUR',
            'default_language' => 'lt',
            'timezone' => 'Europe/Vilnius'
        ]);
        $zone2 = Zone::factory()->create([
            'currency_code' => 'USD',
            'default_language' => 'lt',
            'timezone' => 'Europe/Vilnius'
        ]);
        
        $city1 = City::factory()->create(['zone_id' => $zone1->id]);
        $city2 = City::factory()->create(['zone_id' => $zone2->id]);

        $eurLtVilniusZoneCities = City::byZoneCurrencyLanguageAndTimezone('EUR', 'lt', 'Europe/Vilnius')->get();

        $this->assertTrue($eurLtVilniusZoneCities->contains($city1));
        $this->assertFalse($eurLtVilniusZoneCities->contains($city2));
    }
}