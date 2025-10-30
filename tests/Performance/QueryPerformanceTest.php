<?php

declare(strict_types=1);

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\City;
use App\Models\Country;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Report;
use App\Repositories\MenuRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

function assertQueryCountLessThanOrEqual(int $expected, callable $callback, string $message = ''): void
{
    DB::enableQueryLog();
    DB::flushQueryLog();

    try {
        $callback();
        $queryCount = count(DB::getQueryLog());
    } finally {
        DB::disableQueryLog();
    }

    expect($queryCount)->toBeLessThanOrEqual(
        $expected,
        $message !== '' ? $message : "Expected at most {$expected} queries, got {$queryCount}."
    );
}

test('cities index query stays within eager loading limits', function (): void {
    // Seed a consistent country and several cities to exercise the eager loading logic.
    $country = Country::factory()->create([
        'is_active' => true,
    ]);

    City::factory()
        ->count(5)
        ->forCountry($country)
        ->withTranslations(['en'])
        ->create([
            'is_active' => true,
        ]);

    $retrievedCities = null;

    assertQueryCountLessThanOrEqual(30, function () use (&$retrievedCities, $country): void {
        // Replicate the controller query without rendering the Blade view so we only measure the database work.
        $retrievedCities = City::query()
            ->with([
                'country' => static fn ($query) => $query->withTranslations(),
                'parent'  => static fn ($query) => $query->withTranslations(),
            ])
            ->withTranslations()
            ->active()
            ->enabled()
            ->where('country_id', $country->id)
            ->limit(5)
            ->get();
    }, 'Cities index executed too many queries.');

    // Confirm the eager-loaded relations are actually present so the query bound is meaningful.
    expect($retrievedCities)->not->toBeNull();
    expect($retrievedCities)->toHaveCount(5);
    expect($retrievedCities->first()?->relationLoaded('country'))->toBeTrue();
});

test('attribute values index avoids N+1 regressions', function (): void {
    // Create an attribute up front so attribute value factories share the same parent without extra lookups.
    $attribute = Attribute::factory()->create([
        'is_enabled' => true,
        'is_active'  => true,
    ]);

    AttributeValue::factory()
        ->count(6)
        ->for($attribute, 'attribute')
        ->create([
            'is_enabled' => true,
            'is_active'  => true,
        ]);

    $attributeValues = null;

    assertQueryCountLessThanOrEqual(55, function () use (&$attributeValues): void {
        // Mirror the attribute value listing query, including relationship counts, to surface N+1 regressions reliably.
        $attributeValues = AttributeValue::query()
            ->with([
                'attribute' => static fn ($query) => $query->with('translations'),
                'translations',
            ])
            ->withCount(['products', 'variants'])
            ->enabled()
            ->ordered()
            ->limit(6)
            ->get();
    }, 'Attribute values index executed too many queries.');

    expect($attributeValues)->not->toBeNull();
    expect($attributeValues)->toHaveCount(6);
    expect($attributeValues->first()?->relationLoaded('attribute'))->toBeTrue();
});

test('reports index pagination remains efficient', function (): void {
    // Seed a small batch of public reports to validate the listing query without relying on seeded fixtures.
    Report::factory()
        ->count(6)
        ->public()
        ->active()
        ->create();

    $reports = null;

    assertQueryCountLessThanOrEqual(5, function () use (&$reports): void {
        // Execute the filtering logic directly; rendering the Blade view would otherwise skew query counts heavily.
        $reports = Report::query()
            ->active()
            ->public()
            ->whereNotNull('type')
            ->whereNotNull('category')
            ->whereNotNull('name->' . app()->getLocale())
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
    }, 'Reports index executed too many queries.');

    expect($reports)->not->toBeNull();
    expect($reports)->toHaveCount(6);
});

test('menu repository by key avoids n+1 queries', function (): void {
    // Ensure the cache is empty so we always measure the live database calls.
    Cache::clear();

    // Delete any existing menu with this key to avoid unique constraint violations when factories run observers.
    Menu::withoutGlobalScopes()->where('key', 'main_header')->delete();

    $menu = Menu::factory()->active()->create([
        'key'      => 'main_header',
        'location' => 'header',
    ]);

    $roots = MenuItem::factory()
        ->count(3)
        ->visible()
        ->sequence(fn ($sequence) => ['sort_order' => $sequence->index])
        ->create([
            'menu_id'   => $menu->id,
            'parent_id' => null,
        ]);

    foreach ($roots as $root) {
        MenuItem::factory()->count(2)->visible()->sequence(fn ($sequence) => ['sort_order' => $sequence->index])
            ->create([
                'menu_id'   => $menu->id,
                'parent_id' => $root->id,
            ]);
    }

    assertQueryCountLessThanOrEqual(4, function () use ($menu): void {
        $repository = app(MenuRepository::class);
        $payload = $repository->byKey($menu->key, app()->getLocale());

        expect($payload)->not->toBeNull();
        expect($payload['items'])->toHaveCount(3);
    }, 'Menu repository byKey executed too many queries.');
});

test('menu repository index query count remains bounded', function (): void {
    Cache::clear();

    Menu::withoutGlobalScopes()->whereIn('key', ['main_header', 'footer_links'])->delete();

    $menus = Menu::factory()->count(2)->active()->sequence(
        ['key' => 'main_header', 'location' => 'header'],
        ['key' => 'footer_links', 'location' => 'footer']
    )->create();

    foreach ($menus as $menu) {
        MenuItem::factory()->count(4)->visible()->sequence(fn ($sequence) => ['sort_order' => $sequence->index])
            ->create([
                'menu_id'   => $menu->id,
                'parent_id' => null,
            ]);
    }

    assertQueryCountLessThanOrEqual(12, function (): void {
        $repository = app(MenuRepository::class);
        $collection = $repository->all(null, app()->getLocale());

        expect($collection)->toHaveCount(2);
    }, 'Menu repository all executed too many queries.');
});
