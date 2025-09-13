<?php

declare(strict_types=1);

use App\Http\Controllers\Frontend\CountryController;
use App\Models\Country;
use App\Models\Translations\CountryTranslation;
use App\Models\City;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->activeCountry = Country::factory()->active()->create([
        'name' => 'Lithuania',
        'cca2' => 'LT',
        'cca3' => 'LTU',
        'region' => 'Europe',
        'currency_code' => 'EUR',
        'is_eu_member' => true,
        'requires_vat' => true,
        'vat_rate' => 21.00,
    ]);

    $this->inactiveCountry = Country::factory()->inactive()->create([
        'name' => 'Inactive Country',
        'cca2' => 'IC',
        'cca3' => 'INC',
    ]);
});

it('can display countries index page', function () {
    $response = $this->get(route('frontend.countries.index'));

    $response->assertStatus(200);
    $response->assertViewIs('frontend.countries.index');
    $response->assertSee('Lithuania');
    $response->assertDontSee('Inactive Country');
});

it('can filter countries by region', function () {
    $asianCountry = Country::factory()->active()->create([
        'region' => 'Asia',
        'name' => 'Japan',
    ]);

    $response = $this->get(route('frontend.countries.index', ['region' => 'Europe']));

    $response->assertStatus(200);
    $response->assertSee('Lithuania');
    $response->assertDontSee('Japan');
});

it('can filter countries by currency', function () {
    $usdCountry = Country::factory()->active()->create([
        'currency_code' => 'USD',
        'name' => 'United States',
    ]);

    $response = $this->get(route('frontend.countries.index', ['currency' => 'EUR']));

    $response->assertStatus(200);
    $response->assertSee('Lithuania');
    $response->assertDontSee('United States');
});

it('can filter countries by eu membership', function () {
    $nonEuCountry = Country::factory()->active()->create([
        'is_eu_member' => false,
        'name' => 'United States',
    ]);

    $response = $this->get(route('frontend.countries.index', ['eu_member' => '1']));

    $response->assertStatus(200);
    $response->assertSee('Lithuania');
    $response->assertDontSee('United States');
});

it('can filter countries by vat requirement', function () {
    $noVatCountry = Country::factory()->active()->create([
        'requires_vat' => false,
        'name' => 'United States',
    ]);

    $response = $this->get(route('frontend.countries.index', ['requires_vat' => '1']));

    $response->assertStatus(200);
    $response->assertSee('Lithuania');
    $response->assertDontSee('United States');
});

it('can search countries by name', function () {
    $latvia = Country::factory()->active()->create([
        'name' => 'Latvia',
        'cca2' => 'LV',
    ]);

    $response = $this->get(route('frontend.countries.index', ['search' => 'Lithuania']));

    $response->assertStatus(200);
    $response->assertSee('Lithuania');
    $response->assertDontSee('Latvia');
});

it('can search countries by cca2 code', function () {
    $latvia = Country::factory()->active()->create([
        'name' => 'Latvia',
        'cca2' => 'LV',
    ]);

    $response = $this->get(route('frontend.countries.index', ['search' => 'LT']));

    $response->assertStatus(200);
    $response->assertSee('Lithuania');
    $response->assertDontSee('Latvia');
});

it('can search countries by cca3 code', function () {
    $latvia = Country::factory()->active()->create([
        'name' => 'Latvia',
        'cca3' => 'LVA',
    ]);

    $response = $this->get(route('frontend.countries.index', ['search' => 'LTU']));

    $response->assertStatus(200);
    $response->assertSee('Lithuania');
    $response->assertDontSee('Latvia');
});

it('can search countries by translated name', function () {
    CountryTranslation::factory()->create([
        'country_id' => $this->activeCountry->id,
        'locale' => 'lt',
        'name' => 'Lietuva',
    ]);

    $response = $this->get(route('frontend.countries.index', ['search' => 'Lietuva']));

    $response->assertStatus(200);
    $response->assertSee('Lithuania');
});

it('can sort countries by name', function () {
    $latvia = Country::factory()->active()->create([
        'name' => 'Latvia',
        'sort_order' => 2,
    ]);

    $response = $this->get(route('frontend.countries.index', ['sort' => 'name', 'direction' => 'asc']));

    $response->assertStatus(200);
    $response->assertSeeInOrder(['Latvia', 'Lithuania']);
});

it('can sort countries by region', function () {
    $asia = Country::factory()->active()->create([
        'name' => 'Japan',
        'region' => 'Asia',
    ]);

    $response = $this->get(route('frontend.countries.index', ['sort' => 'region', 'direction' => 'asc']));

    $response->assertStatus(200);
    $response->assertSeeInOrder(['Japan', 'Lithuania']);
});

it('can sort countries by currency', function () {
    $usdCountry = Country::factory()->active()->create([
        'name' => 'United States',
        'currency_code' => 'USD',
    ]);

    $response = $this->get(route('frontend.countries.index', ['sort' => 'currency_code', 'direction' => 'asc']));

    $response->assertStatus(200);
    $response->assertSeeInOrder(['United States', 'Lithuania']);
});

it('can sort countries by vat rate', function () {
    $highVatCountry = Country::factory()->active()->create([
        'name' => 'High VAT Country',
        'vat_rate' => 25.00,
    ]);

    $response = $this->get(route('frontend.countries.index', ['sort' => 'vat_rate', 'direction' => 'desc']));

    $response->assertStatus(200);
    $response->assertSeeInOrder(['High VAT Country', 'Lithuania']);
});

it('can display country show page', function () {
    $response = $this->get(route('frontend.countries.show', $this->activeCountry));

    $response->assertStatus(200);
    $response->assertViewIs('frontend.countries.show');
    $response->assertSee('Lithuania');
    $response->assertSee('LT');
    $response->assertSee('EUR');
    $response->assertSee('21.00%');
});

it('returns 404 for inactive country', function () {
    $response = $this->get(route('frontend.countries.show', $this->inactiveCountry));

    $response->assertStatus(404);
});

it('returns 404 for disabled country', function () {
    $disabledCountry = Country::factory()->create([
        'is_active' => true,
        'is_enabled' => false,
    ]);

    $response = $this->get(route('frontend.countries.show', $disabledCountry));

    $response->assertStatus(404);
});

it('can display country with related data', function () {
    $city = City::factory()->create([
        'country_id' => $this->activeCountry->id,
        'name' => 'Vilnius',
        'is_active' => true,
    ]);

    $address = Address::factory()->create([
        'country_code' => $this->activeCountry->cca2,
    ]);

    $response = $this->get(route('frontend.countries.show', $this->activeCountry));

    $response->assertStatus(200);
    $response->assertSee('Vilnius');
});

it('can get countries json api', function () {
    $response = $this->get(route('frontend.countries.api.search'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'countries' => [
            '*' => [
                'id',
                'name',
                'code',
                'iso_code',
                'currency',
                'phone_code',
                'flag',
            ],
        ],
    ]);

    $data = $response->json();
    expect($data['countries'])->toHaveCount(1);
    expect($data['countries'][0]['name'])->toBe('Lithuania');
    expect($data['countries'][0]['code'])->toBe('LT');
});

it('can search countries via json api', function () {
    $latvia = Country::factory()->active()->create([
        'name' => 'Latvia',
        'cca2' => 'LV',
    ]);

    $response = $this->get(route('frontend.countries.api.search', ['search' => 'LT']));

    $response->assertStatus(200);
    $data = $response->json();
    expect($data['countries'])->toHaveCount(1);
    expect($data['countries'][0]['code'])->toBe('LT');
});

it('limits json api results to 50 countries', function () {
    Country::factory()->active()->count(60)->create();

    $response = $this->get(route('frontend.countries.api.search'));

    $response->assertStatus(200);
    $data = $response->json();
    expect($data['countries'])->toHaveCount(50);
});

it('excludes inactive countries from json api', function () {
    $response = $this->get(route('frontend.countries.api.search'));

    $response->assertStatus(200);
    $data = $response->json();
    expect($data['countries'])->toHaveCount(1);
    expect($data['countries'][0]['name'])->toBe('Lithuania');
});

it('can display country with translations', function () {
    CountryTranslation::factory()->create([
        'country_id' => $this->activeCountry->id,
        'locale' => 'lt',
        'name' => 'Lietuva',
        'name_official' => 'Lietuvos Respublika',
        'description' => 'Šiaurės Europos šalis',
    ]);

    $response = $this->get(route('frontend.countries.show', $this->activeCountry));

    $response->assertStatus(200);
    $response->assertSee('Lietuva');
    $response->assertSee('Lietuvos Respublika');
    $response->assertSee('Šiaurės Europos šalis');
});

it('can handle pagination', function () {
    Country::factory()->active()->count(30)->create();

    $response = $this->get(route('frontend.countries.index'));

    $response->assertStatus(200);
    $response->assertViewHas('countries');
    
    $countries = $response->viewData('countries');
    expect($countries->count())->toBeLessThanOrEqual(24); // Default pagination
});

it('can handle empty search results', function () {
    $response = $this->get(route('frontend.countries.index', ['search' => 'nonexistent']));

    $response->assertStatus(200);
    $response->assertDontSee('Lithuania');
});

it('can handle multiple filters', function () {
    $europeanEurCountry = Country::factory()->active()->create([
        'region' => 'Europe',
        'currency_code' => 'EUR',
        'is_eu_member' => true,
        'name' => 'Germany',
    ]);

    $asianUsdCountry = Country::factory()->active()->create([
        'region' => 'Asia',
        'currency_code' => 'USD',
        'is_eu_member' => false,
        'name' => 'Japan',
    ]);

    $response = $this->get(route('frontend.countries.index', [
        'region' => 'Europe',
        'currency' => 'EUR',
        'eu_member' => '1',
    ]));

    $response->assertStatus(200);
    $response->assertSee('Germany');
    $response->assertDontSee('Japan');
});
