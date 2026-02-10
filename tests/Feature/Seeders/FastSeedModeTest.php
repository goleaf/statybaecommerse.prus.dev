<?php

declare(strict_types=1);

use App\Models\City;
use App\Models\Country;
use Database\Seeders\Cities\CitiesMergedSeeder;
use Database\Seeders\CountrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('country seeder switches to lightweight dataset in fast mode', function (): void {
    config()->set('seeds.fast_mode', true);

    $this->seed(CountrySeeder::class);

    expect(Country::query()->withoutGlobalScopes()->count())->toBe(3);
    expect(
        Country::query()->withoutGlobalScopes()->pluck('cca2')->sort()->values()->all()
    )->toBe(['DE', 'LT', 'US']);
});

test('cities merged seeder honors fast iso and row limits', function (): void {
    config()->set('seeds.fast_mode', true);
    config()->set('seeds.fast.city_iso2', ['LT']);
    config()->set('seeds.fast.max_cities_per_country', 10);
    config()->set('seeds.fast.locales', ['lt', 'en']);

    $this->seed(CountrySeeder::class);
    $this->seed(CitiesMergedSeeder::class);

    $lithuania = Country::query()->withoutGlobalScopes()->where('cca2', 'LT')->firstOrFail();
    $germany = Country::query()->withoutGlobalScopes()->where('cca2', 'DE')->firstOrFail();

    $lithuaniaCityCount = City::query()
        ->withoutGlobalScopes()
        ->where('country_id', $lithuania->id)
        ->count();

    $germanyCityCount = City::query()
        ->withoutGlobalScopes()
        ->where('country_id', $germany->id)
        ->count();

    expect($lithuaniaCityCount)->toBeGreaterThan(0);
    expect($lithuaniaCityCount)->toBeLessThanOrEqual(10);
    expect($germanyCityCount)->toBe(0);
});
