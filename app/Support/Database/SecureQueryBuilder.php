<?php

declare(strict_types=1);

namespace App\Support\Database;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Secure query builder helper to prevent SQL injection in raw queries.
 */
final class SecureQueryBuilder
{
    /**
     * Safely build JSON extraction queries for different database drivers.
     */
    public static function jsonExtract(Builder $query, string $column, string $path, string $operator, mixed $value): Builder
    {
        $driver = $query->getConnection()->getDriverName();

        // Validate column name to prevent injection
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            throw new InvalidArgumentException('Invalid column name for JSON extraction');
        }

        // Validate JSON path to prevent injection
        if (! preg_match('/^\$\.[a-zA-Z_][a-zA-Z0-9_]*$/', $path)) {
            throw new InvalidArgumentException('Invalid JSON path format');
        }

        // Validate operator
        $allowedOperators = ['=', '!=', 'LIKE', 'ILIKE', 'NOT LIKE'];
        if (! in_array(strtoupper($operator), $allowedOperators, true)) {
            throw new InvalidArgumentException('Invalid operator for JSON extraction');
        }

        return match ($driver) {
            'mysql', 'mariadb' => $query->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT({$column}, ?)) {$operator} ?",
                [$path, $value]
            ),
            'pgsql' => $query->whereRaw(
                "({$column}->?) {$operator} ?",
                [str_replace('$.', '', $path), $value]
            ),
            'sqlite' => $query->whereRaw(
                "json_valid({$column}) AND json_extract({$column}, ?) {$operator} ?",
                [$path, $value]
            ),
            default => throw new InvalidArgumentException("Unsupported database driver: {$driver}")
        };
    }

    /**
     * Safely build COALESCE queries for price sorting.
     */
    public static function coalescePriceOrder(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderByRaw("COALESCE(NULLIF(sale_price, 0), price) {$direction}");
    }

    /**
     * Safely build date-based grouping queries.
     */
    public static function dateGroup(Builder $query, string $column, string $format = 'Y-m-d'): Builder
    {
        // Validate column name
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $column)) {
            throw new InvalidArgumentException('Invalid column name for date grouping');
        }

        $driver = $query->getConnection()->getDriverName();

        return match ($driver) {
            'mysql', 'mariadb' => $query->selectRaw("DATE({$column}) as date_group"),
            'pgsql'  => $query->selectRaw("DATE({$column}) as date_group"),
            'sqlite' => $query->selectRaw("DATE({$column}) as date_group"),
            default  => throw new InvalidArgumentException("Unsupported database driver: {$driver}")
        };
    }

    /**
     * Safely build aggregation queries with proper column validation.
     */
    public static function safeAggregate(Builder $query, string $function, string $column, ?string $alias = null): Builder
    {
        // Validate function name
        $allowedFunctions = ['COUNT', 'SUM', 'AVG', 'MIN', 'MAX'];
        if (! in_array(strtoupper($function), $allowedFunctions, true)) {
            throw new InvalidArgumentException('Invalid aggregate function');
        }

        // Validate column name
        if ($column !== '*' && ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $column)) {
            throw new InvalidArgumentException('Invalid column name for aggregation');
        }

        $alias = $alias ?? strtolower($function) . '_' . str_replace('.', '_', $column);

        // Validate alias
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $alias)) {
            throw new InvalidArgumentException('Invalid alias name');
        }

        return $query->selectRaw("{$function}({$column}) as {$alias}");
    }

    /**
     * Safely build EXISTS subqueries with proper validation.
     */
    public static function safeExists(Builder $query, callable $callback): Builder
    {
        return $query->whereExists(function ($subquery) use ($callback) {
            $subquery->select(DB::raw('1'));
            $callback($subquery);
        });
    }
}
