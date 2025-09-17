<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Country;
use App\Models\Translations\CountryTranslation;
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
        ]);

        $this->assertDatabaseHas('countries', [
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
        ]);

        $this->assertEquals('Lithuania', $country->name);
        $this->assertEquals('LT', $country->cca2);
        $this->assertEquals('LTU', $country->cca3);
    }

    public function test_country_has_translations(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'name_official' => 'Republic of Lithuania',
            'description' => 'A country in Northern Europe',
        ]);

        $translation = CountryTranslation::factory()->create([
            'country_id' => $country->id,
            'locale' => 'lt',
            'name' => 'Lietuva',
            'name_official' => 'Lietuvos Respublika',
            'description' => 'Šalis Šiaurės Europoje',
        ]);

        $this->assertTrue($country->hasTranslationFor('lt'));
        $this->assertEquals('Lietuva', $country->getTranslatedName('lt'));
        $this->assertEquals('Lietuvos Respublika', $country->getTranslatedOfficialName('lt'));
        $this->assertEquals('Šalis Šiaurės Europoje', $country->getTranslatedDescription('lt'));
    }

    public function test_country_scope_with_translations(): void
    {
        $country = Country::factory()->create(['name' => 'Germany']);
        CountryTranslation::factory()->create([
            'country_id' => $country->id,
            'locale' => 'de',
            'name' => 'Deutschland',
        ]);

        $countries = Country::withTranslations('de')->get();
        $this->assertCount(1, $countries);
        $this->assertTrue($countries->first()->relationLoaded('translations'));
    }

    public function test_country_available_locales(): void
    {
        $country = Country::factory()->create();
        CountryTranslation::factory()->create([
            'country_id' => $country->id,
            'locale' => 'en',
        ]);
        CountryTranslation::factory()->create([
            'country_id' => $country->id,
            'locale' => 'lt',
        ]);

        $locales = $country->getAvailableLocales();
        $this->assertContains('en', $locales);
        $this->assertContains('lt', $locales);
        $this->assertCount(2, $locales);
    }

    public function test_country_get_or_create_translation(): void
    {
        $country = Country::factory()->create(['name' => 'France']);

        $translation = $country->getOrCreateTranslation('fr');
        $this->assertEquals('France', $translation->name);
        $this->assertEquals('fr', $translation->locale);

        // Should return existing translation
        $existingTranslation = $country->getOrCreateTranslation('fr');
        $this->assertEquals($translation->id, $existingTranslation->id);
    }

    public function test_country_update_translation(): void
    {
        $country = Country::factory()->create();
        CountryTranslation::factory()->create([
            'country_id' => $country->id,
            'locale' => 'es',
            'name' => 'España',
        ]);

        $updated = $country->updateTranslation('es', [
            'name' => 'Reino de España',
            'description' => 'Un país en Europa',
        ]);

        $this->assertTrue($updated);
        $this->assertDatabaseHas('country_translations', [
            'country_id' => $country->id,
            'locale' => 'es',
            'name' => 'Reino de España',
            'description' => 'Un país en Europa',
        ]);
    }

    public function test_country_bulk_update_translations(): void
    {
        $country = Country::factory()->create();

        $translations = [
            'en' => ['name' => 'United Kingdom', 'description' => 'A country in Europe'],
            'lt' => ['name' => 'Jungtinė Karalystė', 'description' => 'Šalis Europoje'],
        ];

        $updated = $country->updateTranslations($translations);
        $this->assertTrue($updated);

        $this->assertDatabaseHas('country_translations', [
            'country_id' => $country->id,
            'locale' => 'en',
            'name' => 'United Kingdom',
        ]);
        $this->assertDatabaseHas('country_translations', [
            'country_id' => $country->id,
            'locale' => 'lt',
            'name' => 'Jungtinė Karalystė',
        ]);
    }

    public function test_country_full_display_name(): void
    {
        $country = Country::factory()->create([
            'name' => 'Poland',
            'phone_calling_code' => '48',
        ]);

        $displayName = $country->getFullDisplayName();
        $this->assertEquals('Poland (+48)', $displayName);

        $countryWithoutPhoneCode = Country::factory()->create([
            'name' => 'Monaco',
            'phone_calling_code' => null,
        ]);

        $displayNameWithoutPhone = $countryWithoutPhoneCode->getFullDisplayName();
        $this->assertEquals('Monaco', $displayNameWithoutPhone);
    }

    public function test_country_coordinates_attribute(): void
    {
        $country = Country::factory()->create([
            'latitude' => 54.6872,
            'longitude' => 25.2797,
        ]);

        $coordinates = $country->getCoordinatesAttribute();
        $this->assertEquals(54.6872, $coordinates['latitude']);
        $this->assertEquals(25.2797, $coordinates['longitude']);
    }

    public function test_country_formatted_currency_info(): void
    {
        $country = Country::factory()->create([
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
            'currencies' => ['EUR' => 'Euro'],
        ]);

        $currencyInfo = $country->getFormattedCurrencyInfo();
        $this->assertEquals('EUR', $currencyInfo['code']);
        $this->assertEquals('€', $currencyInfo['symbol']);
        $this->assertEquals(['EUR' => 'Euro'], $currencyInfo['currencies']);
    }

    public function test_country_formatted_vat_info(): void
    {
        $country = Country::factory()->create([
            'requires_vat' => true,
            'vat_rate' => 21.0,
        ]);

        $vatInfo = $country->getFormattedVatInfo();
        $this->assertTrue($vatInfo['requires_vat']);
        $this->assertEquals(21.0, $vatInfo['vat_rate']);
        $this->assertEquals('21.00%', $vatInfo['formatted_rate']);
    }

    public function test_country_economic_info(): void
    {
        $country = Country::factory()->create([
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'requires_vat' => false,
            'vat_rate' => 0.0,
            'is_eu_member' => false,
        ]);

        $economicInfo = $country->getEconomicInfo();
        $this->assertEquals('USD', $economicInfo['currency']['code']);
        $this->assertFalse($economicInfo['vat']['requires_vat']);
        $this->assertFalse($economicInfo['eu_member']);
    }

    public function test_country_geographic_info(): void
    {
        $country = Country::factory()->create([
            'region' => 'North America',
            'subregion' => 'Northern America',
            'latitude' => 39.8283,
            'longitude' => -98.5795,
            'timezone' => 'America/New_York',
        ]);

        $geographicInfo = $country->getGeographicInfo();
        $this->assertEquals('North America', $geographicInfo['region']);
        $this->assertEquals('Northern America', $geographicInfo['subregion']);
        $this->assertEquals(39.8283, $geographicInfo['coordinates']['latitude']);
        $this->assertEquals(-98.5795, $geographicInfo['coordinates']['longitude']);
        $this->assertEquals('America/New_York', $geographicInfo['timezone']);
    }

    public function test_country_scopes(): void
    {
        Country::factory()->create(['is_active' => true, 'is_enabled' => true, 'is_eu_member' => false, 'requires_vat' => false]);
        Country::factory()->create(['is_active' => true, 'is_enabled' => true, 'is_eu_member' => false, 'requires_vat' => false]);
        Country::factory()->create(['is_active' => true, 'is_enabled' => false, 'is_eu_member' => true, 'requires_vat' => false]);
        Country::factory()->create(['is_active' => true, 'is_enabled' => false, 'is_eu_member' => false, 'requires_vat' => true]);

        $this->assertCount(4, Country::active()->get());
        $this->assertCount(2, Country::enabled()->get());
        $this->assertCount(1, Country::euMembers()->get());
        $this->assertCount(1, Country::where('requires_vat', true)->get());
    }

    public function test_country_helper_methods(): void
    {
        $country = Country::factory()->create([
            'is_active' => true,
            'is_eu_member' => true,
            'requires_vat' => true,
            'vat_rate' => 20.0,
        ]);

        $this->assertTrue($country->isActive());
        $this->assertTrue($country->isEuMember());
        $this->assertTrue($country->requiresVat());
        $this->assertEquals(20.0, $country->getVatRate());
        $this->assertEquals('20.00%', $country->getFormattedVatRate());
    }

    public function test_country_relations(): void
    {
        $country = Country::factory()->create();

        // Test that relations exist and are properly configured
        // Note: regions() method was removed as regions functionality was removed from the system
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $country->cities());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $country->addresses());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $country->users());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $country->zones());
    }
}
