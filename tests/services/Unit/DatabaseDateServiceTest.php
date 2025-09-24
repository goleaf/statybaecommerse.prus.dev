<?php

declare(strict_types=1);

use App\Services\DatabaseDateService;
use Tests\TestCase;

uses(TestCase::class);

it('returns correct date expression by driver', function (string $driver, string $expected) {
    config()->set('database.default', $driver);

    expect(DatabaseDateService::dateExpression('created_at'))
        ->toBe($expected);
})->with([
    ['sqlite', 'DATE(created_at)'],
    ['mysql', 'DATE(created_at)'],
    ['mariadb', 'DATE(created_at)'],
    ['pgsql', 'DATE(created_at)'],
]);

it('returns correct hour expression by driver', function (string $driver, string $expected) {
    config()->set('database.default', $driver);
    expect(DatabaseDateService::hourExpression('created_at'))->toBe($expected);
})->with([
    ['sqlite', "CAST(strftime('%H', created_at) AS INTEGER)"],
    ['mysql', 'HOUR(created_at)'],
    ['mariadb', 'HOUR(created_at)'],
    ['pgsql', 'EXTRACT(HOUR FROM created_at)'],
]);
