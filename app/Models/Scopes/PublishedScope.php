<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope to filter only published records
 * 
 * This scope automatically applies to models that have published_at fields
 * and ensures that only published records are returned by default.
 */
final class PublishedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has a published_at column
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'published_at')) {
            $builder->whereNotNull('published_at')
                   ->where('published_at', '<=', now());
        }
    }
}
