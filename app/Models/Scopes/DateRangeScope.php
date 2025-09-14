<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope for date range filtering
 * 
 * This scope automatically applies date range filtering to models that have
 * date fields like created_at, updated_at, or custom date fields.
 */
final class DateRangeScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has date-related columns
        $dateColumns = $this->getDateColumns($model);
        
        if (!empty($dateColumns)) {
            $this->applyDateFilters($builder, $dateColumns);
        }
    }

    /**
     * Get date-related columns for the model
     */
    private function getDateColumns(Model $model): array
    {
        $table = $model->getTable();
        $schema = $model->getConnection()->getSchemaBuilder();
        
        $dateColumns = [];
        
        // Check for common date column names
        $possibleColumns = [
            'created_at', 
            'updated_at', 
            'published_at', 
            'scheduled_at',
            'expires_at',
            'starts_at',
            'ends_at'
        ];
        
        foreach ($possibleColumns as $column) {
            if ($schema->hasColumn($table, $column)) {
                $dateColumns[] = $column;
            }
        }
        
        return $dateColumns;
    }

    /**
     * Apply date filters based on column type
     */
    private function applyDateFilters(Builder $builder, array $dateColumns): void
    {
        foreach ($dateColumns as $column) {
            $this->applyColumnSpecificFilter($builder, $column);
        }
    }

    /**
     * Apply column-specific date filtering
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
                    $query->whereNull($column)
                          ->orWhere($column, '>', now());
                });
                break;
                
            case 'scheduled_at':
                // Only show scheduled content that's ready
                $builder->where(function ($query) use ($column) {
                    $query->whereNull($column)
                          ->orWhere($column, '<=', now());
                });
                break;
                
            case 'starts_at':
            case 'ends_at':
                // Handle campaign-like date ranges
                if ($column === 'starts_at') {
                    $builder->where(function ($query) use ($column) {
                        $query->whereNull($column)
                              ->orWhere($column, '<=', now());
                    });
                } elseif ($column === 'ends_at') {
                    $builder->where(function ($query) use ($column) {
                        $query->whereNull($column)
                              ->orWhere($column, '>=', now());
                    });
                }
                break;
                
            default:
                // For created_at, updated_at, etc., no additional filtering
                break;
        }
    }
}
