<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope to filter only approved records
 * 
 * This scope automatically applies to models that have is_approved fields
 * and ensures that only approved records are returned by default.
 */
final class ApprovedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has an is_approved column
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_approved')) {
            $builder->where('is_approved', true);
        }
    }
}
