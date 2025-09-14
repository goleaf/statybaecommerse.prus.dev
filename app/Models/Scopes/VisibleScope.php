<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope to filter only visible records
 * 
 * This scope automatically applies to models that have is_visible fields
 * and ensures that only visible records are returned by default.
 */
final class VisibleScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has an is_visible column
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_visible')) {
            $builder->where('is_visible', true);
        }
    }
}
