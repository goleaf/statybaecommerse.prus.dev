<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Zone;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_zone_can_be_created(): void
    {
        $zone = Zone::factory()->create([
            'name' => 'European Union',
            'code' => 'EU',
            'type' => 'economic',
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
            'default_language' => 'en',
            'timezone' => 'Europe/Brussels',
            'phone_code' => '+32',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('zones', [
            'name' => 'European Union',
            'code' => 'EU',
            'type' => 'economic',
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
            'default_language' => 'en',
            'timezone' => 'Europe/Brussels',
            'phone_code' => '+32',
            'is_active' => true,
        ]);
    }

    public function test_zone_has_many_cities(): void
    {
        $zone = Zone::factory()->create();
        $city1 = City::factory()->create(['zone_id' => $zone->id]);
        $city2 = City::factory()->create(['zone_id' => $zone->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $zone->cities);
        $this->assertCount(2, $zone->cities);
        $this->assertTrue($zone->cities->contains($city1));
        $this->assertTrue($zone->cities->contains($city2));
    }

    public function test_zone_has_many_countries(): void
    {
        $zone = Zone::factory()->create();
        $country1 = Country::factory()->create(['zone_id' => $zone->id]);
        $country2 = Country::factory()->create(['zone_id' => $zone->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $zone->countries);
        $this->assertCount(2, $zone->countries);
        $this->assertTrue($zone->countries->contains($country1));
        $this->assertTrue($zone->countries->contains($country2));
    }

    public function test_zone_has_many_regions(): void
    {
        $zone = Zone::factory()->create();
        $region1 = Region::factory()->create(['zone_id' => $zone->id]);
        $region2 = Region::factory()->create(['zone_id' => $zone->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $zone->regions);
        $this->assertCount(2, $zone->regions);
        $this->assertTrue($zone->regions->contains($region1));
        $this->assertTrue($zone->regions->contains($region2));
    }

    public function test_zone_casts_work_correctly(): void
    {
        $zone = Zone::factory()->create([
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 5,
            'created_at' => now(),
        ]);

        $this->assertIsBool($zone->is_active);
        $this->assertIsBool($zone->is_default);
        $this->assertIsInt($zone->sort_order);
        $this->assertInstanceOf(\Carbon\Carbon::class, $zone->created_at);
    }

    public function test_zone_fillable_attributes(): void
    {
        $zone = new Zone();
        $fillable = $zone->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('code', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('currency_code', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_zone_scope_active(): void
    {
        $activeZone = Zone::factory()->create(['is_active' => true]);
        $inactiveZone = Zone::factory()->create(['is_active' => false]);

        $activeZones = Zone::active()->get();

        $this->assertTrue($activeZones->contains($activeZone));
        $this->assertFalse($activeZones->contains($inactiveZone));
    }

    public function test_zone_scope_default(): void
    {
        $defaultZone = Zone::factory()->create(['is_default' => true]);
        $nonDefaultZone = Zone::factory()->create(['is_default' => false]);

        $defaultZones = Zone::default()->get();

        $this->assertTrue($defaultZones->contains($defaultZone));
        $this->assertFalse($defaultZones->contains($nonDefaultZone));
    }

    public function test_zone_scope_ordered(): void
    {
        $zone1 = Zone::factory()->create(['sort_order' => 2]);
        $zone2 = Zone::factory()->create(['sort_order' => 1]);
        $zone3 = Zone::factory()->create(['sort_order' => 3]);

        $orderedZones = Zone::ordered()->get();

        $this->assertEquals($zone2->id, $orderedZones->first()->id);
        $this->assertEquals($zone3->id, $orderedZones->last()->id);
    }

    public function test_zone_can_have_description(): void
    {
        $zone = Zone::factory()->create([
            'description' => 'European Union economic zone',
        ]);

        $this->assertEquals('European Union economic zone', $zone->description);
    }

    public function test_zone_can_have_currency(): void
    {
        $zone = Zone::factory()->create([
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
            'currency_name' => 'Euro',
        ]);

        $this->assertEquals('EUR', $zone->currency_code);
        $this->assertEquals('€', $zone->currency_symbol);
        $this->assertEquals('Euro', $zone->currency_name);
    }

    public function test_zone_can_have_language(): void
    {
        $zone = Zone::factory()->create([
            'default_language' => 'en',
            'language_name' => 'English',
        ]);

        $this->assertEquals('en', $zone->default_language);
        $this->assertEquals('English', $zone->language_name);
    }

    public function test_zone_can_have_timezone(): void
    {
        $zone = Zone::factory()->create([
            'timezone' => 'Europe/Brussels',
        ]);

        $this->assertEquals('Europe/Brussels', $zone->timezone);
    }

    public function test_zone_can_have_phone_code(): void
    {
        $zone = Zone::factory()->create([
            'phone_code' => '+32',
        ]);

        $this->assertEquals('+32', $zone->phone_code);
    }

    public function test_zone_can_have_metadata(): void
    {
        $zone = Zone::factory()->create([
            'metadata' => [
                'created_by' => 'admin',
                'version' => '1.0',
                'tags' => ['eu', 'economic', 'zone'],
            ],
        ]);

        $this->assertIsArray($zone->metadata);
        $this->assertEquals('admin', $zone->metadata['created_by']);
        $this->assertEquals('1.0', $zone->metadata['version']);
        $this->assertIsArray($zone->metadata['tags']);
    }

    public function test_zone_can_have_scope_by_code(): void
    {
        $zone1 = Zone::factory()->create(['code' => 'EU']);
        $zone2 = Zone::factory()->create(['code' => 'US']);

        $euZones = Zone::byCode('EU')->get();

        $this->assertTrue($euZones->contains($zone1));
        $this->assertFalse($euZones->contains($zone2));
    }

    public function test_zone_can_have_scope_by_type(): void
    {
        $zone1 = Zone::factory()->create(['type' => 'economic']);
        $zone2 = Zone::factory()->create(['type' => 'political']);

        $economicZones = Zone::byType('economic')->get();

        $this->assertTrue($economicZones->contains($zone1));
        $this->assertFalse($economicZones->contains($zone2));
    }

    public function test_zone_can_have_scope_by_currency(): void
    {
        $zone1 = Zone::factory()->create(['currency_code' => 'EUR']);
        $zone2 = Zone::factory()->create(['currency_code' => 'USD']);

        $eurZones = Zone::byCurrency('EUR')->get();

        $this->assertTrue($eurZones->contains($zone1));
        $this->assertFalse($eurZones->contains($zone2));
    }

    public function test_zone_can_have_scope_by_language(): void
    {
        $zone1 = Zone::factory()->create(['default_language' => 'en']);
        $zone2 = Zone::factory()->create(['default_language' => 'fr']);

        $enZones = Zone::byLanguage('en')->get();

        $this->assertTrue($enZones->contains($zone1));
        $this->assertFalse($enZones->contains($zone2));
    }

    public function test_zone_can_have_scope_by_timezone(): void
    {
        $zone1 = Zone::factory()->create(['timezone' => 'Europe/Brussels']);
        $zone2 = Zone::factory()->create(['timezone' => 'America/New_York']);

        $brusselsZones = Zone::byTimezone('Europe/Brussels')->get();

        $this->assertTrue($brusselsZones->contains($zone1));
        $this->assertFalse($brusselsZones->contains($zone2));
    }

    public function test_zone_can_have_scope_by_phone_code(): void
    {
        $zone1 = Zone::factory()->create(['phone_code' => '+32']);
        $zone2 = Zone::factory()->create(['phone_code' => '+1']);

        $belgiumZones = Zone::byPhoneCode('+32')->get();

        $this->assertTrue($belgiumZones->contains($zone1));
        $this->assertFalse($belgiumZones->contains($zone2));
    }

    public function test_zone_can_have_scope_by_currency_and_language(): void
    {
        $zone1 = Zone::factory()->create(['currency_code' => 'EUR', 'default_language' => 'en']);
        $zone2 = Zone::factory()->create(['currency_code' => 'USD', 'default_language' => 'en']);

        $eurEnZones = Zone::byCurrencyAndLanguage('EUR', 'en')->get();

        $this->assertTrue($eurEnZones->contains($zone1));
        $this->assertFalse($eurEnZones->contains($zone2));
    }

    public function test_zone_can_have_scope_by_currency_and_timezone(): void
    {
        $zone1 = Zone::factory()->create(['currency_code' => 'EUR', 'timezone' => 'Europe/Brussels']);
        $zone2 = Zone::factory()->create(['currency_code' => 'USD', 'timezone' => 'Europe/Brussels']);

        $eurBrusselsZones = Zone::byCurrencyAndTimezone('EUR', 'Europe/Brussels')->get();

        $this->assertTrue($eurBrusselsZones->contains($zone1));
        $this->assertFalse($eurBrusselsZones->contains($zone2));
    }

    public function test_zone_can_have_scope_by_language_and_timezone(): void
    {
        $zone1 = Zone::factory()->create(['default_language' => 'en', 'timezone' => 'Europe/Brussels']);
        $zone2 = Zone::factory()->create(['default_language' => 'fr', 'timezone' => 'Europe/Brussels']);

        $enBrusselsZones = Zone::byLanguageAndTimezone('en', 'Europe/Brussels')->get();

        $this->assertTrue($enBrusselsZones->contains($zone1));
        $this->assertFalse($enBrusselsZones->contains($zone2));
    }

    public function test_zone_can_have_scope_by_currency_language_and_timezone(): void
    {
        $zone1 = Zone::factory()->create([
            'currency_code' => 'EUR',
            'default_language' => 'en',
            'timezone' => 'Europe/Brussels'
        ]);
        $zone2 = Zone::factory()->create([
            'currency_code' => 'USD',
            'default_language' => 'en',
            'timezone' => 'Europe/Brussels'
        ]);

        $eurEnBrusselsZones = Zone::byCurrencyLanguageAndTimezone('EUR', 'en', 'Europe/Brussels')->get();

        $this->assertTrue($eurEnBrusselsZones->contains($zone1));
        $this->assertFalse($eurEnBrusselsZones->contains($zone2));
    }
}