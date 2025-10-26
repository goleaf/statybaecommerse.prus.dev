<?php

declare(strict_types=1);

namespace App\Support\Filament\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class SingleDateFilter
{
    public static function apply(Builder $query, ?string $value, string $column, bool $withTime = false): Builder
    {
        if (blank($value)) {
            return $query;
        }

        $date = Carbon::parse($value);

        if ($withTime) {
            return $query->where($column, '=', $date);
        }

        return $query->whereDate($column, '=', $date);
    }
}
