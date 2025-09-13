<?php

declare(strict_types=1);

use App\Models\Country;
use App\Models\Translations\CountryTranslation;
use App\Models\Address;
use App\Models\City;
use App\Models\Region;
use App\Models\User;
use App\Models\Customer;
use App\Models\ShippingZone;
use App\Models\TaxRate;
use App\Models\Currency;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->country = Country::factory()->create([
        'name' => 'Lithuania',
        'cca2' => 'LT',
        'cca3' => 'LTU',
        'ccn3' => '440',
        'currency_code' => 'EUR',
        'currency_symbol' => '€',
        'phone_calling_code' => '370',
        'region' => 'Europe',
        'subregion' => 'Northern Europe',
        'latitude' => 55.1694,
        'longitude' => 23.8813,
        'is_active' => true,
        'is_enabled' => true,
        'is_eu_member' => true,
        'requires_vat' => true,
        'vat_rate' => 21.00,
        'sort_order' => 1,
    ]);
});

it('can be created with factory', function () {
    expect($this->country)->toBeInstanceOf(Country::class);
    expect($this->country->name)->toBe('Lithuania');
    expect($this->country->cca2)->toBe('LT');
    expect($this->country->cca3)->toBe('LTU');
});

it('has correct fillable attributes', function () {
    $fillable = [
        'name',
        'name_official',
        'cca2',
        'cca3',
        'ccn3',
        'code',
        'iso_code',
        'currency_code',
        'currency_symbol',
        'phone_code',
        'phone_calling_code',
        'flag',
        'svg_flag',
        'region',
        'subregion',
        'latitude',
        'longitude',
        'currencies',
        'languages',
        'timezones',
        'is_active',
        'is_eu_member',
        'requires_vat',
        'vat_rate',
        'timezone',
        'description',
        'metadata',
        'is_enabled',
        'sort_order',
    ];

    expect($this->country->getFillable())->toBe($fillable);
});

it('has correct casts', function () {
    expect($this->country->getCasts())->toHaveKey('latitude', 'decimal:8');
    expect($this->country->getCasts())->toHaveKey('longitude', 'decimal:8');
    expect($this->country->getCasts())->toHaveKey('currencies', 'array');
    expect($this->country->getCasts())->toHaveKey('languages', 'array');
    expect($this->country->getCasts())->toHaveKey('timezones', 'array');
    expect($this->country->getCasts())->toHaveKey('is_active', 'boolean');
    expect($this->country->getCasts())->toHaveKey('is_eu_member', 'boolean');
    expect($this->country->getCasts())->toHaveKey('requires_vat', 'boolean');
    expect($this->country->getCasts())->toHaveKey('vat_rate', 'decimal:2');
    expect($this->country->getCasts())->toHaveKey('metadata', 'array');
    expect($this->country->getCasts())->toHaveKey('is_enabled', 'boolean');
    expect($this->country->getCasts())->toHaveKey('sort_order', 'integer');
});

it('has translations relationship', function () {
    $translation = CountryTranslation::factory()->create([
        'country_id' => $this->country->id,
        'locale' => 'lt',
        'name' => 'Lietuva',
        'name_official' => 'Lietuvos Respublika',
        'description' => 'Šiaurės Europos šalis',
    ]);

    expect($this->country->translations)->toHaveCount(1);
    expect($this->country->translations->first())->toBeInstanceOf(CountryTranslation::class);
    expect($this->country->translations->first()->name)->toBe('Lietuva');
});

it('can get translated name', function () {
    CountryTranslation::factory()->create([
        'country_id' => $this->country->id,
        'locale' => 'lt',
        'name' => 'Lietuva',
    ]);

    expect($this->country->translated_name)->toBe('Lietuva');
});

it('can get translated official name', function () {
    CountryTranslation::factory()->create([
        'country_id' => $this->country->id,
        'locale' => 'lt',
        'name_official' => 'Lietuvos Respublika',
    ]);

    expect($this->country->translated_official_name)->toBe('Lietuvos Respublika');
});

it('can get translated description', function () {
    CountryTranslation::factory()->create([
        'country_id' => $this->country->id,
        'locale' => 'lt',
        'description' => 'Šiaurės Europos šalis',
    ]);

    expect($this->country->translated_description)->toBe('Šiaurės Europos šalis');
});

it('has addresses relationship', function () {
    $address = Address::factory()->create([
        'country_code' => $this->country->cca2,
    ]);

    expect($this->country->addresses)->toHaveCount(1);
    expect($this->country->addresses->first())->toBeInstanceOf(Address::class);
});

it('has cities relationship', function () {
    $city = City::factory()->create([
        'country_id' => $this->country->id,
    ]);

    expect($this->country->cities)->toHaveCount(1);
    expect($this->country->cities->first())->toBeInstanceOf(City::class);
});

it('has regions relationship', function () {
    $region = Region::factory()->create([
        'country_id' => $this->country->id,
    ]);

    expect($this->country->regions)->toHaveCount(1);
    expect($this->country->regions->first())->toBeInstanceOf(Region::class);
});

it('has users relationship', function () {
    $user = User::factory()->create([
        'country_code' => $this->country->cca2,
    ]);

    expect($this->country->users)->toHaveCount(1);
    expect($this->country->users->first())->toBeInstanceOf(User::class);
});

it('has customers relationship', function () {
    $customer = Customer::factory()->create([
        'country_code' => $this->country->cca2,
    ]);

    expect($this->country->customers)->toHaveCount(1);
    expect($this->country->customers->first())->toBeInstanceOf(Customer::class);
});

it('has shipping zones relationship', function () {
    $shippingZone = ShippingZone::factory()->create();
    $this->country->shippingZones()->attach($shippingZone);

    expect($this->country->shippingZones)->toHaveCount(1);
    expect($this->country->shippingZones->first())->toBeInstanceOf(ShippingZone::class);
});

it('has tax rates relationship', function () {
    $taxRate = TaxRate::factory()->create([
        'country_code' => $this->country->cca2,
    ]);

    expect($this->country->taxRates)->toHaveCount(1);
    expect($this->country->taxRates->first())->toBeInstanceOf(TaxRate::class);
});

it('has currencies relationship', function () {
    $currency = Currency::factory()->create();
    $this->country->currencies()->attach($currency);

    expect($this->country->currencies)->toHaveCount(1);
    expect($this->country->currencies->first())->toBeInstanceOf(Currency::class);
});

it('has zones relationship', function () {
    $zone = Zone::factory()->create();
    $this->country->zones()->attach($zone);

    expect($this->country->zones)->toHaveCount(1);
    expect($this->country->zones->first())->toBeInstanceOf(Zone::class);
});

it('can check if active', function () {
    expect($this->country->isActive())->toBeTrue();

    $this->country->update(['is_active' => false]);
    expect($this->country->isActive())->toBeFalse();
});

it('can check if eu member', function () {
    expect($this->country->isEuMember())->toBeTrue();

    $this->country->update(['is_eu_member' => false]);
    expect($this->country->isEuMember())->toBeFalse();
});

it('can check if requires vat', function () {
    expect($this->country->requiresVat())->toBeTrue();

    $this->country->update(['requires_vat' => false]);
    expect($this->country->requiresVat())->toBeFalse();
});

it('can get vat rate', function () {
    expect($this->country->getVatRate())->toBe(21.00);

    $this->country->update(['vat_rate' => null]);
    expect($this->country->getVatRate())->toBeNull();
});

it('can get formatted vat rate', function () {
    expect($this->country->getFormattedVatRate())->toBe('21.00%');

    $this->country->update(['vat_rate' => null]);
    expect($this->country->getFormattedVatRate())->toBe('N/A');
});

it('can get full address', function () {
    $fullAddress = $this->country->getFullAddress();
    expect($fullAddress)->toContain('Lithuania');
    expect($fullAddress)->toContain('Europe');
    expect($fullAddress)->toContain('Northern Europe');
});

it('can get flag url', function () {
    $this->country->update(['flag' => 'lt.png']);
    expect($this->country->getFlagUrl())->toBe(asset('flags/lt.png'));

    $this->country->update(['flag' => null]);
    expect($this->country->getFlagUrl())->toBeNull();
});

it('can get svg flag url', function () {
    $this->country->update(['svg_flag' => 'lt.svg']);
    expect($this->country->getSvgFlagUrl())->toBe(asset('flags/svg/lt.svg'));

    $this->country->update(['svg_flag' => null]);
    expect($this->country->getSvgFlagUrl())->toBeNull();
});

it('can scope active countries', function () {
    Country::factory()->create(['is_active' => false]);
    
    $activeCountries = Country::active()->get();
    expect($activeCountries)->toHaveCount(1);
    expect($activeCountries->first()->id)->toBe($this->country->id);
});

it('can scope enabled countries', function () {
    Country::factory()->create(['is_enabled' => false]);
    
    $enabledCountries = Country::enabled()->get();
    expect($enabledCountries)->toHaveCount(1);
    expect($enabledCountries->first()->id)->toBe($this->country->id);
});

it('can scope eu members', function () {
    Country::factory()->create(['is_eu_member' => false]);
    
    $euMembers = Country::euMembers()->get();
    expect($euMembers)->toHaveCount(1);
    expect($euMembers->first()->id)->toBe($this->country->id);
});

it('can scope countries requiring vat', function () {
    Country::factory()->create(['requires_vat' => false]);
    
    $vatCountries = Country::requiresVat()->get();
    expect($vatCountries)->toHaveCount(1);
    expect($vatCountries->first()->id)->toBe($this->country->id);
});

it('can scope by region', function () {
    Country::factory()->create(['region' => 'Asia']);
    
    $europeanCountries = Country::byRegion('Europe')->get();
    expect($europeanCountries)->toHaveCount(1);
    expect($europeanCountries->first()->id)->toBe($this->country->id);
});

it('can scope by currency', function () {
    Country::factory()->create(['currency_code' => 'USD']);
    
    $eurCountries = Country::byCurrency('EUR')->get();
    expect($eurCountries)->toHaveCount(1);
    expect($eurCountries->first()->id)->toBe($this->country->id);
});

it('has display name attribute', function () {
    expect($this->country->display_name)->toBe('Lithuania (+370)');

    $this->country->update(['phone_calling_code' => null]);
    expect($this->country->display_name)->toBe('Lithuania');
});

it('has code attribute', function () {
    expect($this->country->code)->toBe('LT');
});

it('has iso code attribute', function () {
    expect($this->country->iso_code)->toBe('LTU');
});

it('has phone code attribute', function () {
    expect($this->country->phone_code)->toBe('370');
});
