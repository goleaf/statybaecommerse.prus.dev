<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CategoryTranslation;
use Database\Seeders\CategorySeeder;
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

test('category seeder trims tree size and locale count in fast mode', function (): void {
    config()->set('seeds.fast_mode', true);
    config()->set('seeds.fast.max_root_categories', 3);
    config()->set('seeds.fast.max_children_per_category', 2);
    config()->set('seeds.fast.locales', ['lt', 'en']);

    $this->seed(CategorySeeder::class);

    $categoryCount = Category::query()->withoutGlobalScopes()->count();

    expect($categoryCount)->toBeGreaterThan(0);
    expect($categoryCount)->toBeLessThan(128);
    expect(
        CategoryTranslation::query()->distinct()->pluck('locale')->sort()->values()->all()
    )->toBe(['en', 'lt']);
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
