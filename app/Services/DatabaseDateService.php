<?php

declare(strict_types=1);

namespace App\Services;

/**
 * DatabaseDateService
 *
 * Service class containing DatabaseDateService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class DatabaseDateService
{
    /**
     * Handle dateExpression functionality with proper error handling.
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
     * Handle hourExpression functionality with proper error handling.
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
     * Handle monthExpression functionality with proper error handling.
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
     * Handle yearExpression functionality with proper error handling.
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
     * Handle dayExpression functionality with proper error handling.
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
     * Handle minuteExpression functionality with proper error handling.
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
     * Handle secondExpression functionality with proper error handling.
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
