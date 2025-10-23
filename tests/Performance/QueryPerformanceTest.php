<?php

declare(strict_types=1);

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\City;
use App\Models\Country;
use App\Models\Report;
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
        'is_active' => true,
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
