<?php

declare(strict_types=1);

use App\Models\Country;
use App\Models\Translations\CountryTranslation;
use App\Models\User;

beforeEach(function () {
    $this->adminUser = User::factory()->create([
        'email' => 'admin@test.com',
        'name' => 'Admin User',
    ]);

    // Assign admin role if roles exist
    if (class_exists(\Spatie\Permission\Models\Role::class)) {
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        $this->adminUser->assignRole($adminRole);
    }
});

it('can create a country model', function () {
    $country = Country::create([
        'cca2' => 'LT',
        'cca3' => 'LTU',
        'phone_calling_code' => '370',
        'flag' => '🇱🇹',
        'region' => 'Europe',
        'subregion' => 'Northern Europe',
        'latitude' => 55.169438,
        'longitude' => 23.881275,
        'currencies' => ['EUR'],
        'is_enabled' => true,
        'sort_order' => 1,
    ]);

    expect($country->cca2)->toBe('LT');
    expect($country->cca3)->toBe('LTU');
    expect($country->currencies)->toBe(['EUR']);
    expect($country->is_enabled)->toBeTrue();

    $this->assertDatabaseHas('countries', [
        'cca2' => 'LT',
        'cca3' => 'LTU',
        'phone_calling_code' => '370',
        'region' => 'Europe',
    ]);
});

it('can create country translations', function () {
    $country = Country::create([
        'cca2' => 'LT',
        'cca3' => 'LTU',
        'phone_calling_code' => '370',
        'flag' => '🇱🇹',
        'region' => 'Europe',
        'subregion' => 'Northern Europe',
        'latitude' => 55.169438,
        'longitude' => 23.881275,
        'currencies' => ['EUR'],
        'is_enabled' => true,
        'sort_order' => 1,
    ]);

    $translation = CountryTranslation::create([
        'country_id' => $country->id,
        'locale' => 'en',
        'name' => 'Lithuania',
        'name_official' => 'Republic of Lithuania',
    ]);

    expect($translation->name)->toBe('Lithuania');
    expect($translation->name_official)->toBe('Republic of Lithuania');

    $this->assertDatabaseHas('country_translations', [
        'country_id' => $country->id,
        'locale' => 'en',
        'name' => 'Lithuania',
        'name_official' => 'Republic of Lithuania',
    ]);
});

it('has translations relationship', function () {
    $country = Country::create([
        'cca2' => 'LT',
        'cca3' => 'LTU',
        'phone_calling_code' => '370',
        'flag' => '🇱🇹',
        'region' => 'Europe',
        'subregion' => 'Northern Europe',
        'latitude' => 55.169438,
        'longitude' => 23.881275,
        'currencies' => ['EUR'],
        'is_enabled' => true,
        'sort_order' => 1,
    ]);

    CountryTranslation::create([
        'country_id' => $country->id,
        'locale' => 'en',
        'name' => 'Lithuania',
        'name_official' => 'Republic of Lithuania',
    ]);

    CountryTranslation::create([
        'country_id' => $country->id,
        'locale' => 'lt',
        'name' => 'Lietuva',
        'name_official' => 'Lietuvos Respublika',
    ]);

    $country->load('translations');

    expect($country->translations)->toHaveCount(2);
    expect($country->translations->where('locale', 'en')->first()->name)->toBe('Lithuania');
    expect($country->translations->where('locale', 'lt')->first()->name)->toBe('Lietuva');
});

it('returns translated name via trans method', function () {
    $country = Country::create([
        'cca2' => 'LT',
        'cca3' => 'LTU',
        'phone_calling_code' => '370',
        'flag' => '🇱🇹',
        'region' => 'Europe',
        'subregion' => 'Northern Europe',
        'latitude' => 55.169438,
        'longitude' => 23.881275,
        'currencies' => ['EUR'],
        'is_enabled' => true,
        'sort_order' => 1,
    ]);

    CountryTranslation::create([
        'country_id' => $country->id,
        'locale' => 'en',
        'name' => 'Lithuania',
        'name_official' => 'Republic of Lithuania',
    ]);

    CountryTranslation::create([
        'country_id' => $country->id,
        'locale' => 'lt',
        'name' => 'Lietuva',
        'name_official' => 'Lietuvos Respublika',
    ]);

    $country->load('translations');

    // Test English translation
    app()->setLocale('en');
    expect($country->trans('name'))->toBe('Lithuania');
    expect($country->trans('name_official'))->toBe('Republic of Lithuania');

    // Test Lithuanian translation
    app()->setLocale('lt');
    expect($country->trans('name'))->toBe('Lietuva');
    expect($country->trans('name_official'))->toBe('Lietuvos Respublika');
});

it('has display name attribute with phone code', function () {
    $country = Country::create([
        'cca2' => 'LT',
        'cca3' => 'LTU',
        'phone_calling_code' => '370',
        'flag' => '🇱🇹',
        'region' => 'Europe',
        'subregion' => 'Northern Europe',
        'latitude' => 55.169438,
        'longitude' => 23.881275,
        'currencies' => ['EUR'],
        'is_enabled' => true,
        'sort_order' => 1,
    ]);

    CountryTranslation::create([
        'country_id' => $country->id,
        'locale' => 'en',
        'name' => 'Lithuania',
        'name_official' => 'Republic of Lithuania',
    ]);

    $country->load('translations');
    app()->setLocale('en');

    expect($country->display_name)->toBe('Lithuania (+370)');
});

it('can access admin routes when authenticated', function () {
    $this->actingAs($this->adminUser);

    // Test countries index route
    $response = $this->get(route('filament.admin.resources.countries.index'));
    $response->assertStatus(200);

    // Test countries create route
    $response = $this->get(route('filament.admin.resources.countries.create'));
    $response->assertStatus(200);
});

it('displays countries in admin index', function () {
    $this->actingAs($this->adminUser);

    // Create test country
    $lithuania = Country::create([
        'cca2' => 'LT',
        'cca3' => 'LTU',
        'phone_calling_code' => '370',
        'flag' => '🇱🇹',
        'region' => 'Europe',
        'subregion' => 'Northern Europe',
        'latitude' => 55.169438,
        'longitude' => 23.881275,
        'currencies' => ['EUR'],
        'is_enabled' => true,
        'sort_order' => 1,
    ]);

    CountryTranslation::create([
        'country_id' => $lithuania->id,
        'locale' => 'en',
        'name' => 'Lithuania',
        'name_official' => 'Republic of Lithuania',
    ]);

    $response = $this->get(route('filament.admin.resources.countries.index'));
    $response->assertStatus(200);
    $response->assertSee('LT');
    $response->assertSee('🇱🇹');
    $response->assertSee('Europe');
});

it('supports soft deletes', function () {
    $country = Country::create([
        'cca2' => 'LT',
        'cca3' => 'LTU',
        'phone_calling_code' => '370',
        'flag' => '🇱🇹',
        'region' => 'Europe',
        'subregion' => 'Northern Europe',
        'latitude' => 55.169438,
        'longitude' => 23.881275,
        'currencies' => ['EUR'],
        'is_enabled' => true,
        'sort_order' => 1,
    ]);

    $country->delete();

    $this->assertSoftDeleted('countries', ['id' => $country->id]);
    expect(Country::find($country->id))->toBeNull();
    expect(Country::withTrashed()->find($country->id))->not->toBeNull();
});
