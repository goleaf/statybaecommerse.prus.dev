<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope to filter only active/enabled records
 * 
 * This scope automatically applies to models that have is_active, is_enabled, or is_visible fields
 * and ensures that only active records are returned by default.
 */
final class ActiveScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check for different active field names in order of preference
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_active')) {
            $builder->where('is_active', true);
        } elseif ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_enabled')) {
            $builder->where('is_enabled', true);
        } elseif ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_visible')) {
            $builder->where('is_visible', true);
        }
    }
}
