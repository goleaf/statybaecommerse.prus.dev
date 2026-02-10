<?php

declare(strict_types=1);

namespace Tests\Unit\Support\ImportExport;

use App\Support\ImportExport\ProgressCounter;

it('normalizes total and processed counters to safe bounds', function (): void {
    expect(ProgressCounter::normalizeTotal(-10))->toBe(0)
        ->and(ProgressCounter::normalizeTotal(12))->toBe(12)
        ->and(ProgressCounter::normalizeProcessed(-2, 10))->toBe(0)
        ->and(ProgressCounter::normalizeProcessed(20, 10))->toBe(10)
        ->and(ProgressCounter::normalizeProcessed(7, 10))->toBe(7);
});

it('calculates failed rows from processed minus successful using safe bounds', function (): void {
    expect(ProgressCounter::failedRows(0, 0, 100))->toBe(0)
        ->and(ProgressCounter::failedRows(40, 35, 100))->toBe(5)
        ->and(ProgressCounter::failedRows(120, 40, 100))->toBe(60)
        ->and(ProgressCounter::failedRows(10, 50, 100))->toBe(0);
});

it('calculates percent from normalized counters', function (): void {
    expect(ProgressCounter::percent(0, 0))->toBe(0)
        ->and(ProgressCounter::percent(25, 100))->toBe(25)
        ->and(ProgressCounter::percent(250, 100))->toBe(100)
        ->and(ProgressCounter::percent(-1, 100))->toBe(0);
});
