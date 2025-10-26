<?php

declare(strict_types=1);

namespace App\Support\Filament\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class DateRangeFilter
{
    /**
     * @param array{start?: string|null, end?: string|null}|null $range
     */
    public static function apply(Builder $query, ?array $range, string $column, bool $withTime = false): Builder
    {
        if (! is_array($range)) {
            return $query;
        }

        $start = filled($range['start'] ?? null)
            ? Carbon::parse($range['start'])->when(
                ! $withTime,
                fn (Carbon $date): Carbon => $date->startOfDay(),
            )
            : null;

        $end = filled($range['end'] ?? null)
            ? Carbon::parse($range['end'])->when(
                ! $withTime,
                fn (Carbon $date): Carbon => $date->endOfDay(),
            )
            : null;

        if ($start && $end) {
            return $query->whereBetween($column, [$start, $end]);
        }

        if ($start) {
            return $query->where($column, '>=', $start);
        }

        if ($end) {
            return $query->where($column, '<=', $end);
        }

        return $query;
    }
}
