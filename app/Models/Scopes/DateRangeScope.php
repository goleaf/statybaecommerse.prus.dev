<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * DateRangeScope
 *
 * Eloquent model representing the DateRangeScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DateRangeScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DateRangeScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DateRangeScope query()
 *
 * @mixin \Eloquent
 */
final class DateRangeScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has date-related columns
        $dateColumns = $this->getDateColumns($model);
        if (! empty($dateColumns)) {
            $this->applyDateFilters($builder, $dateColumns);
        }
    }

    /**
     * Handle getDateColumns functionality with proper error handling.
     */
    private function getDateColumns(Model $model): array
    {
        $table = $model->getTable();
        $schema = $model->getConnection()->getSchemaBuilder();
        $dateColumns = [];
        // Check for common date column names
        $possibleColumns = ['created_at', 'updated_at', 'published_at', 'scheduled_at', 'expires_at', 'starts_at', 'ends_at'];
        foreach ($possibleColumns as $column) {
            if ($schema->hasColumn($table, $column)) {
                $dateColumns[] = $column;
            }
        }

        return $dateColumns;
    }

    /**
     * Handle applyDateFilters functionality with proper error handling.
     */
    private function applyDateFilters(Builder $builder, array $dateColumns): void
    {
        foreach ($dateColumns as $column) {
            $this->applyColumnSpecificFilter($builder, $column);
        }
    }

    /**
     * Handle applyColumnSpecificFilter functionality with proper error handling.
     */
    private function applyColumnSpecificFilter(Builder $builder, string $column): void
    {
        switch ($column) {
            case 'published_at':
                // Only show published content
                $builder->whereNotNull($column)->where($column, '<=', now());
                break;
            case 'expires_at':
                // Only show non-expired content
                $builder->where(function ($query) use ($column) {
                    $query->whereNull($column)->orWhere($column, '>', now());
                });
                break;
            case 'scheduled_at':
                // Only show scheduled content that's ready
                $builder->where(function ($query) use ($column) {
                    $query->whereNull($column)->orWhere($column, '<=', now());
                });
                break;
            case 'starts_at':
            case 'ends_at':
                // Handle campaign-like date ranges
                if ($column === 'starts_at') {
                    $builder->where(function ($query) use ($column) {
                        $query->whereNull($column)->orWhere($column, '<=', now());
                    });
                } elseif ($column === 'ends_at') {
                    $builder->where(function ($query) use ($column) {
                        $query->whereNull($column)->orWhere($column, '>=', now());
                    });
                }
                break;
            default:
                // For created_at, updated_at, etc., no additional filtering
                break;
        }
    }
}
