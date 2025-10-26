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

test('cities index maintains a stable query count', function (): void {
    $country = Country::factory()->create();
    City::factory()->count(5)->forCountry($country)->withTranslations()->create();

    assertQueryCountLessThanOrEqual(12, function (): void {
        get(route('cities.index'))->assertOk();
    }, 'Cities index executed too many queries.');
});

test('attribute values index maintains a stable query count', function (): void {
    $attribute = Attribute::factory()->create([
        'is_enabled' => true,
        'is_active'  => true,
    ]);

    AttributeValue::factory()->count(6)->for($attribute, 'attribute')->create();

    assertQueryCountLessThanOrEqual(12, function (): void {
        get(route('attribute-values.index'))->assertOk();
    }, 'Attribute values index executed too many queries.');
});

test('reports index maintains a stable query count', function (): void {
    Report::factory()->count(6)->public()->active()->create();

    assertQueryCountLessThanOrEqual(10, function (): void {
        get(route('reports.index'))->assertOk();
    }, 'Reports index executed too many queries.');
});

test('menu repository by key avoids n+1 queries', function (): void {
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

    assertQueryCountLessThanOrEqual(4, function (): void {
        $repository = app(MenuRepository::class);
        $payload = $repository->byKey('main_header', app()->getLocale());

        expect($payload)->not->toBeNull();
        expect($payload['items'])->toHaveCount(3);
    }, 'Menu repository byKey executed too many queries.');
});

test('menu repository index query count remains bounded', function (): void {
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

    assertQueryCountLessThanOrEqual(5, function (): void {
        $repository = app(MenuRepository::class);
        $collection = $repository->all(null, app()->getLocale());

        expect($collection)->toHaveCount(2);
    }, 'Menu repository all executed too many queries.');
});
