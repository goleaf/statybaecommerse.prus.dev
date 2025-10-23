<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

trait InteractsWithDateFilter
{
    /**
     * Normalize a filter value into a Carbon date range.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function getDateRange(?string $filter): array
    {
        $filter = $this->normaliseFilter($filter);

        $now = CarbonImmutable::now();

        return match ($filter) {
            'today'        => [$now->startOfDay(), $now->endOfDay()],
            'week'         => [$now->startOfWeek(), $now->endOfWeek()],
            'month'        => [$now->startOfMonth(), $now->endOfMonth()],
            'quarter'      => [$now->firstOfQuarter()->startOfDay(), $now->lastOfQuarter()->endOfDay()],
            'year'         => [$now->startOfYear(), $now->endOfYear()],
            'ytd'          => [$now->startOfYear(), $now->endOfDay()],
            'last_30_days' => [$now->subDays(29)->startOfDay(), $now->endOfDay()],
            'custom'       => $this->resolveCustomRange($now),
            default        => [$now->startOfMonth(), $now->endOfMonth()],
        };
    }

    protected function normaliseFilter(?string $filter): string
    {
        $filter = $filter ?? 'month';

        if (! method_exists($this, 'getFilters')) {
            return $filter;
        }

        $filters = $this->getFilters();

        if ($filters === null || array_key_exists($filter, $filters)) {
            return $filter;
        }

        // Default to the first available filter for unknown inputs, but when a
        // "custom" option is present prefer the explicit month range to avoid
        // invoking arbitrary custom ranges in fallback scenarios.
        $first = array_key_first($filters) ?? 'month';

        if (array_key_exists('custom', $filters) && array_key_exists('month', $filters)) {
            return 'month';
        }

        return $first;
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function resolveCustomRange(CarbonImmutable $fallback): array
    {
        if (method_exists($this, 'getCustomDateRange')) {
            $range = $this->getCustomDateRange();

            if (is_array($range) && count($range) === 2) {
                [$from, $to] = $range;

                if ($from instanceof CarbonInterface && $to instanceof CarbonInterface) {
                    $start = CarbonImmutable::instance($from)->startOfDay();
                    $end = CarbonImmutable::instance($to)->endOfDay();

                    if ($start->greaterThan($end)) {
                        return [$end, $start];
                    }

                    return [$start, $end];
                }
            }
        }

        return [$fallback->startOfMonth(), $fallback->endOfMonth()];
    }
}
