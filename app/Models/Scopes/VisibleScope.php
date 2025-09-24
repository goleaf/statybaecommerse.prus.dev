<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * VisibleScope
 *
 * Eloquent model representing the VisibleScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VisibleScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisibleScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisibleScope query()
 *
 * @mixin \Eloquent
 */
final class VisibleScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has an is_visible column
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_visible')) {
            $builder->where('is_visible', true);
        }
    }
}
