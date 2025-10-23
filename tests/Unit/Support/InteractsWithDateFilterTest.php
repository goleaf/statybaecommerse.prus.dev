<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Filament\Support\InteractsWithDateFilter;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

final class InteractsWithDateFilterTest extends TestCase
{
    public function test_normalises_unknown_filter_to_first_option(): void
    {
        $target = new class {
            use InteractsWithDateFilter { normaliseFilter as public traitNormaliseFilter; }

            public function getFilters(): array
            {
                return [
                    'today' => 'Today',
                    'month' => 'This Month',
                ];
            }
        };

        self::assertSame('today', $target->traitNormaliseFilter('not-a-real-filter'));
    }

    public function test_custom_range_is_sorted_and_fallbacks_on_invalid(): void
    {
        $target = new class {
            use InteractsWithDateFilter { getDateRange as public traitGetDateRange; }

            public function getFilters(): array
            {
                return ['custom' => 'Custom', 'month' => 'Month'];
            }

            public function getCustomDateRange(): array
            {
                // Intentionally reverse order to ensure helper sorts correctly
                return [CarbonImmutable::now()->endOfDay(), CarbonImmutable::now()->subDays(3)->startOfDay()];
            }
        };

        [$start, $end] = $target->traitGetDateRange('custom');
        self::assertInstanceOf(CarbonImmutable::class, $start);
        self::assertInstanceOf(CarbonImmutable::class, $end);
        self::assertTrue($start->lessThanOrEqualTo($end));

        // Invalid filter falls back to default month range
        [$start2, $end2] = $target->traitGetDateRange('unknown');
        self::assertTrue($start2->isSameDay($start2->startOfMonth()));
        self::assertTrue($end2->isSameDay($end2->endOfMonth()));
    }
}

