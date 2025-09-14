<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB as Database;

final /**
 * DatabaseDateService
 * 
 * Service class containing business logic and external integrations.
 */
class DatabaseDateService
{
    /**
     * Get database-agnostic date extraction expression
     */
    public static function dateExpression(string $column = 'created_at'): string
    {
        return match (config('database.default')) {
            'sqlite' => "DATE({$column})",
            'mysql', 'mariadb' => "DATE({$column})",
            'pgsql' => "DATE({$column})",
            default => "DATE({$column})",
        };
    }

    /**
     * Get database-agnostic hour extraction expression
     */
    public static function hourExpression(string $column = 'created_at'): string
    {
        return match (config('database.default')) {
            'sqlite' => "CAST(strftime('%H', {$column}) AS INTEGER)",
            'mysql', 'mariadb' => "HOUR({$column})",
            'pgsql' => "EXTRACT(HOUR FROM {$column})",
            default => "CAST(strftime('%H', {$column}) AS INTEGER)",
        };
    }

    /**
     * Get database-agnostic month extraction expression
     */
    public static function monthExpression(string $column = 'created_at'): string
    {
        return match (config('database.default')) {
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            'mysql', 'mariadb' => "MONTH({$column})",
            'pgsql' => "EXTRACT(MONTH FROM {$column})",
            default => "CAST(strftime('%m', {$column}) AS INTEGER)",
        };
    }

    /**
     * Get database-agnostic year extraction expression
     */
    public static function yearExpression(string $column = 'created_at'): string
    {
        return match (config('database.default')) {
            'sqlite' => "CAST(strftime('%Y', {$column}) AS INTEGER)",
            'mysql', 'mariadb' => "YEAR({$column})",
            'pgsql' => "EXTRACT(YEAR FROM {$column})",
            default => "CAST(strftime('%Y', {$column}) AS INTEGER)",
        };
    }

    /**
     * Get database-agnostic day extraction expression
     */
    public static function dayExpression(string $column = 'created_at'): string
    {
        return match (config('database.default')) {
            'sqlite' => "CAST(strftime('%d', {$column}) AS INTEGER)",
            'mysql', 'mariadb' => "DAY({$column})",
            'pgsql' => "EXTRACT(DAY FROM {$column})",
            default => "CAST(strftime('%d', {$column}) AS INTEGER)",
        };
    }

    /**
     * Get database-agnostic minute extraction expression
     */
    public static function minuteExpression(string $column = 'created_at'): string
    {
        return match (config('database.default')) {
            'sqlite' => "CAST(strftime('%M', {$column}) AS INTEGER)",
            'mysql', 'mariadb' => "MINUTE({$column})",
            'pgsql' => "EXTRACT(MINUTE FROM {$column})",
            default => "CAST(strftime('%M', {$column}) AS INTEGER)",
        };
    }

    /**
     * Get database-agnostic second extraction expression
     */
    public static function secondExpression(string $column = 'created_at'): string
    {
        return match (config('database.default')) {
            'sqlite' => "CAST(strftime('%S', {$column}) AS INTEGER)",
            'mysql', 'mariadb' => "SECOND({$column})",
            'pgsql' => "EXTRACT(SECOND FROM {$column})",
            default => "CAST(strftime('%S', {$column}) AS INTEGER)",
        };
    }
}
