<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope to filter only enabled records
 * 
 * This scope automatically applies to models that have is_enabled fields
 * and ensures that only enabled records are returned by default.
 */
final class EnabledScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has an is_enabled column
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_enabled')) {
            $builder->where('is_enabled', true);
        }
    }
}
