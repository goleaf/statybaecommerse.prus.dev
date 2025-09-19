<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Region;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZoneRegionCityTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_zone_with_translations(): void
    {
        // Create currency first
        $currency = Currency::factory()->create([
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
        ]);

        // Create zone
        $zone = Zone::create([
            'name' => 'Test Zone',
            'slug' => 'test-zone',
            'code' => 'TZ',
            'description' => 'Test zone description',
            'currency_id' => $currency->id,
            'tax_rate' => 21.0,
            'shipping_rate' => 5.99,
            'sort_order' => 1,
            'is_enabled' => true,
            'is_default' => false,
        ]);

        // Create translations
        $zone->translations()->create([
            'locale' => 'lt',
            'name' => 'Testo zona',
            'description' => 'Testo zonos aprašymas',
        ]);

        $zone->translations()->create([
            'locale' => 'en',
            'name' => 'Test Zone',
            'description' => 'Test zone description',
        ]);

        $this->assertDatabaseHas('zones', [
            'code' => 'TZ',
            'name' => 'Test Zone',
        ]);

        $this->assertDatabaseHas('zone_translations', [
            'zone_id' => $zone->id,
            'locale' => 'lt',
            'name' => 'Testo zona',
        ]);

        $this->assertDatabaseHas('zone_translations', [
            'zone_id' => $zone->id,
            'locale' => 'en',
            'name' => 'Test Zone',
        ]);
    }

    public function test_can_create_region_with_translations(): void
    {
        // Create country and zone first
        $country = Country::factory()->create([
            'cca2' => 'LT',
            'name' => 'Lithuania',
        ]);

        $zone = Zone::factory()->create([
            'code' => 'LT',
            'name' => 'Lithuania Zone',
        ]);

        // Create region
        $region = Region::create([
            'name' => 'Vilnius County',
            'slug' => 'vilnius-county',
            'code' => 'LT-VL',
            'description' => 'Vilnius County description',
            'country_id' => $country->id,
            'zone_id' => $zone->id,
            'level' => 1,
            'sort_order' => 1,
            'is_enabled' => true,
            'is_default' => false,
        ]);

        // Create translations
        $region->translations()->create([
            'locale' => 'lt',
            'name' => 'Vilniaus apskritis',
            'description' => 'Vilniaus apskrities aprašymas',
        ]);

        $region->translations()->create([
            'locale' => 'en',
            'name' => 'Vilnius County',
            'description' => 'Vilnius County description',
        ]);

        $this->assertDatabaseHas('regions', [
            'code' => 'LT-VL',
            'name' => 'Vilnius County',
        ]);

        $this->assertDatabaseHas('region_translations', [
            'region_id' => $region->id,
            'locale' => 'lt',
            'name' => 'Vilniaus apskritis',
        ]);

        $this->assertDatabaseHas('region_translations', [
            'region_id' => $region->id,
            'locale' => 'en',
            'name' => 'Vilnius County',
        ]);
    }

    public function test_can_create_city_with_translations(): void
    {
        // Create country, zone, and region first
        $country = Country::factory()->create([
            'cca2' => 'LT',
            'name' => 'Lithuania',
        ]);

        $zone = Zone::factory()->create([
            'code' => 'LT',
            'name' => 'Lithuania Zone',
        ]);

        $region = Region::factory()->create([
            'code' => 'LT-VL',
            'name' => 'Vilnius County',
            'country_id' => $country->id,
            'zone_id' => $zone->id,
        ]);

        // Create city
        $city = City::create([
            'name' => 'Vilnius',
            'slug' => 'vilnius',
            'code' => 'LT-VLN',
            'description' => 'Capital of Lithuania',
            'country_id' => $country->id,
            'zone_id' => $zone->id,
            'region_id' => $region->id,
            'level' => 0,
            'latitude' => 54.6872,
            'longitude' => 25.2797,
            'population' => 588412,
            'postal_codes' => ['01001', '01002', '01003'],
            'sort_order' => 1,
            'is_enabled' => true,
            'is_default' => false,
            'is_capital' => true,
        ]);

        // Create translations
        $city->translations()->create([
            'locale' => 'lt',
            'name' => 'Vilnius',
            'description' => 'Lietuvos sostinė',
        ]);

        $city->translations()->create([
            'locale' => 'en',
            'name' => 'Vilnius',
            'description' => 'Capital of Lithuania',
        ]);

        $this->assertDatabaseHas('cities', [
            'code' => 'LT-VLN',
            'name' => 'Vilnius',
            'is_capital' => true,
        ]);

        $this->assertDatabaseHas('city_translations', [
            'city_id' => $city->id,
            'locale' => 'lt',
            'name' => 'Vilnius',
        ]);

        $this->assertDatabaseHas('city_translations', [
            'city_id' => $city->id,
            'locale' => 'en',
            'name' => 'Vilnius',
        ]);
    }

    public function test_zone_relationships_work(): void
    {
        $currency = Currency::factory()->create();
        $country = Country::factory()->create();
        
        $zone = Zone::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $region = Region::factory()->create([
            'zone_id' => $zone->id,
            'country_id' => $country->id,
        ]);

        $city = City::factory()->create([
            'zone_id' => $zone->id,
            'region_id' => $region->id,
            'country_id' => $country->id,
        ]);

        // Test relationships
        $this->assertEquals($currency->id, $zone->currency->id);
        $this->assertTrue($zone->regions->contains($region));
        $this->assertTrue($zone->cities->contains($city));
    }

    public function test_region_relationships_work(): void
    {
        $country = Country::factory()->create();
        $zone = Zone::factory()->create();
        
        $region = Region::factory()->create([
            'country_id' => $country->id,
            'zone_id' => $zone->id,
        ]);

        $city = City::factory()->create([
            'region_id' => $region->id,
            'country_id' => $country->id,
            'zone_id' => $zone->id,
        ]);

        // Test relationships
        $this->assertEquals($country->id, $region->country->id);
        $this->assertEquals($zone->id, $region->zone->id);
        $this->assertTrue($region->cities->contains($city));
    }

    public function test_city_relationships_work(): void
    {
        $country = Country::factory()->create();
        $zone = Zone::factory()->create();
        $region = Region::factory()->create([
            'country_id' => $country->id,
            'zone_id' => $zone->id,
        ]);
        
        $city = City::factory()->create([
            'country_id' => $country->id,
            'zone_id' => $zone->id,
            'region_id' => $region->id,
        ]);

        // Test relationships
        $this->assertEquals($country->id, $city->country->id);
        $this->assertEquals($zone->id, $city->zone->id);
        $this->assertEquals($region->id, $city->region->id);
    }

    public function test_hierarchical_relationships_work(): void
    {
        $country = Country::factory()->create();
        $zone = Zone::factory()->create();
        
        // Create parent region
        $parentRegion = Region::factory()->create([
            'country_id' => $country->id,
            'zone_id' => $zone->id,
            'level' => 0,
        ]);

        // Create child region
        $childRegion = Region::factory()->create([
            'country_id' => $country->id,
            'zone_id' => $zone->id,
            'parent_id' => $parentRegion->id,
            'level' => 1,
        ]);

        // Create parent city
        $parentCity = City::factory()->create([
            'country_id' => $country->id,
            'zone_id' => $zone->id,
            'region_id' => $parentRegion->id,
            'level' => 0,
        ]);

        // Create child city
        $childCity = City::factory()->create([
            'country_id' => $country->id,
            'zone_id' => $zone->id,
            'region_id' => $parentRegion->id,
            'parent_id' => $parentCity->id,
            'level' => 1,
        ]);

        // Test hierarchical relationships
        $this->assertEquals($parentRegion->id, $childRegion->parent->id);
        $this->assertTrue($parentRegion->children->contains($childRegion));
        
        $this->assertEquals($parentCity->id, $childCity->parent->id);
        $this->assertTrue($parentCity->children->contains($childCity));
    }
}

