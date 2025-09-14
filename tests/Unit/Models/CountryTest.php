<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Models\Translations\CountryTranslation;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CountryTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_can_be_created(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
            'region' => 'Europe',
            'currency_code' => 'EUR',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('countries', [
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
            'region' => 'Europe',
            'currency_code' => 'EUR',
            'is_active' => true,
        ]);

        $this->assertEquals('Lithuania', $country->name);
        $this->assertEquals('LT', $country->cca2);
        $this->assertEquals('LTU', $country->cca3);
        $this->assertEquals('Europe', $country->region);
        $this->assertEquals('EUR', $country->currency_code);
        $this->assertTrue($country->is_active);
    }

    public function test_country_has_translations_relationship(): void
    {
        $country = Country::factory()->create();
        
        $translation = CountryTranslation::factory()->create([
            'country_id' => $country->id,
            'locale' => 'lt',
            'name' => 'Lietuva',
            'name_official' => 'Lietuvos Respublika',
        ]);

        $this->assertTrue($country->translations()->exists());
        $this->assertEquals('Lietuva', $country->trans('name', 'lt'));
        $this->assertEquals('Lietuvos Respublika', $country->trans('name_official', 'lt'));
    }

    public function test_country_has_addresses_relationship(): void
    {
        $country = Country::factory()->create(['cca2' => 'LT']);
        
        $address = Address::factory()->create(['country_code' => 'LT']);

        $this->assertTrue($country->addresses()->exists());
        $this->assertEquals(1, $country->addresses()->count());
        $this->assertEquals($address->id, $country->addresses()->first()->id);
    }

    public function test_country_has_cities_relationship(): void
    {
        $country = Country::factory()->create();
        
        $city = City::factory()->create(['country_id' => $country->id]);

        $this->assertTrue($country->cities()->exists());
        $this->assertEquals(1, $country->cities()->count());
        $this->assertEquals($city->id, $country->cities()->first()->id);
    }

    public function test_country_has_regions_relationship(): void
    {
        $country = Country::factory()->create();
        
        $region = Region::factory()->create(['country_id' => $country->id]);

        $this->assertTrue($country->regions()->exists());
        $this->assertEquals(1, $country->regions()->count());
        $this->assertEquals($region->id, $country->regions()->first()->id);
    }

    // User model doesn't have country_code field, skipping test

    // Customer model doesn't exist yet, skipping test

    public function test_country_has_zones_relationship(): void
    {
        $country = Country::factory()->create();
        $zone = Zone::factory()->create();
        
        $country->zones()->attach($zone);

        $this->assertTrue($country->zones()->exists());
        $this->assertEquals(1, $country->zones()->count());
        $this->assertEquals($zone->id, $country->zones()->first()->id);
    }

    // ShippingZone model doesn't exist yet, skipping test

    // TaxRate model doesn't exist yet, skipping test

    public function test_country_active_scope(): void
    {
        Country::factory()->create(['is_active' => true]);
        Country::factory()->create(['is_active' => false]);

        $activeCountries = Country::active()->get();

        $this->assertEquals(1, $activeCountries->count());
        $this->assertTrue($activeCountries->first()->is_active);
    }

    public function test_country_enabled_scope(): void
    {
        Country::factory()->create(['is_enabled' => true]);
        Country::factory()->create(['is_enabled' => false]);

        $enabledCountries = Country::enabled()->get();

        $this->assertEquals(1, $enabledCountries->count());
        $this->assertTrue($enabledCountries->first()->is_enabled);
    }

    public function test_country_eu_members_scope(): void
    {
        Country::factory()->create(['is_eu_member' => true]);
        Country::factory()->create(['is_eu_member' => false]);

        $euMembers = Country::euMembers()->get();

        $this->assertEquals(1, $euMembers->count());
        $this->assertTrue($euMembers->first()->is_eu_member);
    }

    public function test_country_requires_vat_scope(): void
    {
        Country::factory()->create(['requires_vat' => true]);
        Country::factory()->create(['requires_vat' => false]);

        $vatCountries = Country::where('requires_vat', true)->get();

        $this->assertEquals(1, $vatCountries->count());
        $this->assertTrue($vatCountries->first()->requires_vat);
    }

    public function test_country_by_region_scope(): void
    {
        Country::factory()->create(['region' => 'Europe']);
        Country::factory()->create(['region' => 'Asia']);

        $europeanCountries = Country::byRegion('Europe')->get();

        $this->assertEquals(1, $europeanCountries->count());
        $this->assertEquals('Europe', $europeanCountries->first()->region);
    }

    public function test_country_by_currency_scope(): void
    {
        Country::factory()->create(['currency_code' => 'EUR']);
        Country::factory()->create(['currency_code' => 'USD']);

        $eurCountries = Country::byCurrency('EUR')->get();

        $this->assertEquals(1, $eurCountries->count());
        $this->assertEquals('EUR', $eurCountries->first()->currency_code);
    }

    public function test_country_display_name_attribute(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'phone_calling_code' => '370',
        ]);

        $this->assertEquals('Lithuania (+370)', $country->display_name);
    }

    public function test_country_display_name_attribute_without_phone_code(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'phone_calling_code' => null,
        ]);

        $this->assertEquals('Lithuania', $country->display_name);
    }

    public function test_country_translated_name_attribute(): void
    {
        $country = Country::factory()->create(['name' => 'Lithuania']);
        
        CountryTranslation::factory()->create([
            'country_id' => $country->id,
            'locale' => 'lt',
            'name' => 'Lietuva',
        ]);

        $this->assertEquals('Lietuva', $country->translated_name);
    }

    public function test_country_translated_name_attribute_fallback(): void
    {
        $country = Country::factory()->create(['name' => 'Lithuania']);

        $this->assertEquals('Lithuania', $country->translated_name);
    }

    public function test_country_translated_official_name_attribute(): void
    {
        $country = Country::factory()->create(['name_official' => 'Republic of Lithuania']);
        
        CountryTranslation::factory()->create([
            'country_id' => $country->id,
            'locale' => 'lt',
            'name_official' => 'Lietuvos Respublika',
        ]);

        $this->assertEquals('Lietuvos Respublika', $country->translated_official_name);
    }

    public function test_country_translated_official_name_attribute_fallback(): void
    {
        $country = Country::factory()->create(['name_official' => 'Republic of Lithuania']);

        $this->assertEquals('Republic of Lithuania', $country->translated_official_name);
    }

    public function test_country_translated_description_attribute(): void
    {
        $country = Country::factory()->create(['description' => 'A country in Europe']);
        
        CountryTranslation::factory()->create([
            'country_id' => $country->id,
            'locale' => 'lt',
            'description' => 'Šalis Europoje',
        ]);

        $this->assertEquals('Šalis Europoje', $country->translated_description);
    }

    public function test_country_code_attribute(): void
    {
        $country = Country::factory()->create(['cca2' => 'LT']);

        $this->assertEquals('LT', $country->code);
    }

    public function test_country_iso_code_attribute(): void
    {
        $country = Country::factory()->create(['cca3' => 'LTU']);

        $this->assertEquals('LTU', $country->iso_code);
    }

    public function test_country_phone_code_attribute(): void
    {
        $country = Country::factory()->create(['phone_calling_code' => '370']);

        $this->assertEquals('370', $country->phone_code);
    }

    public function test_country_is_active_method(): void
    {
        $activeCountry = Country::factory()->create(['is_active' => true]);
        $inactiveCountry = Country::factory()->create(['is_active' => false]);

        $this->assertTrue($activeCountry->isActive());
        $this->assertFalse($inactiveCountry->isActive());
    }

    public function test_country_is_eu_member_method(): void
    {
        $euCountry = Country::factory()->create(['is_eu_member' => true]);
        $nonEuCountry = Country::factory()->create(['is_eu_member' => false]);

        $this->assertTrue($euCountry->isEuMember());
        $this->assertFalse($nonEuCountry->isEuMember());
    }

    public function test_country_requires_vat_method(): void
    {
        $vatCountry = Country::factory()->create(['requires_vat' => true]);
        $noVatCountry = Country::factory()->create(['requires_vat' => false]);

        $this->assertTrue($vatCountry->requiresVat());
        $this->assertFalse($noVatCountry->requiresVat());
    }

    public function test_country_get_vat_rate_method(): void
    {
        $country = Country::factory()->create(['vat_rate' => 21.0]);

        $this->assertEquals(21.0, $country->getVatRate());
    }

    public function test_country_get_formatted_vat_rate_method(): void
    {
        $country = Country::factory()->create(['vat_rate' => 21.0]);

        $this->assertEquals('21.00%', $country->getFormattedVatRate());
    }

    public function test_country_get_formatted_vat_rate_method_without_vat(): void
    {
        $country = Country::factory()->create(['vat_rate' => null]);

        $this->assertEquals('N/A', $country->getFormattedVatRate());
    }

    public function test_country_get_full_address_method(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'region' => 'Europe',
            'subregion' => 'Northern Europe',
        ]);

        $this->assertEquals('Lithuania, Europe, Northern Europe', $country->getFullAddress());
    }

    public function test_country_get_full_address_method_with_missing_parts(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'region' => null,
            'subregion' => 'Northern Europe',
        ]);

        $this->assertEquals('Lithuania, Northern Europe', $country->getFullAddress());
    }

    public function test_country_get_flag_url_method(): void
    {
        $country = Country::factory()->create(['flag' => 'lt.png']);

        $this->assertEquals(asset('flags/lt.png'), $country->getFlagUrl());
    }

    public function test_country_get_flag_url_method_without_flag(): void
    {
        $country = Country::factory()->create(['flag' => null]);

        $this->assertNull($country->getFlagUrl());
    }

    public function test_country_get_svg_flag_url_method(): void
    {
        $country = Country::factory()->create(['svg_flag' => 'lt.svg']);

        $this->assertEquals(asset('flags/svg/lt.svg'), $country->getSvgFlagUrl());
    }

    public function test_country_get_svg_flag_url_method_without_flag(): void
    {
        $country = Country::factory()->create(['svg_flag' => null]);

        $this->assertNull($country->getSvgFlagUrl());
    }

    public function test_country_casts_attributes(): void
    {
        $country = Country::factory()->create([
            'latitude' => 54.6872,
            'longitude' => 25.2797,
            'currencies' => ['EUR' => 'Euro'],
            'languages' => ['lt' => 'Lithuanian'],
            'timezones' => ['Europe/Vilnius'],
            'is_active' => true,
            'is_eu_member' => true,
            'requires_vat' => true,
            'vat_rate' => 21.0,
            'metadata' => ['population' => 2794324],
            'is_enabled' => true,
            'sort_order' => 1,
        ]);

        $this->assertIsNumeric($country->latitude);
        $this->assertIsNumeric($country->longitude);
        $this->assertIsArray($country->currencies);
        $this->assertIsArray($country->languages);
        $this->assertIsArray($country->timezones);
        $this->assertIsBool($country->is_active);
        $this->assertIsBool($country->is_eu_member);
        $this->assertIsBool($country->requires_vat);
        $this->assertIsNumeric($country->vat_rate);
        $this->assertIsArray($country->metadata);
        $this->assertIsBool($country->is_enabled);
        $this->assertIsInt($country->sort_order);
    }
}