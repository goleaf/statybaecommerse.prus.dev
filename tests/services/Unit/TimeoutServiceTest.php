<?php

declare(strict_types=1);

use App\Services\TimeoutService;
use Illuminate\Support\LazyCollection;
use Tests\TestCase;

uses(TestCase::class);

it('withTimeout yields empty when start time + negative seconds is in the past', function () {
    $col = LazyCollection::make(function () {
        for ($i = 0; $i < 1000; $i++) {
            yield $i;
        }
    });

    $start = now()->subSeconds(5);
    $out = TimeoutService::withTimeout($col, -1, $start);

    expect($out->count())->toBe(0);
});

it('getRemainingTime is zero for past timeout', function () {
    $past = now()->subSeconds(10);
    expect(TimeoutService::getRemainingTime($past))->toBe(0);
});
