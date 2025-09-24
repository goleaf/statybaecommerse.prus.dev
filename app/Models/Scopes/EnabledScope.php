<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * EnabledScope
 *
 * Eloquent model representing the EnabledScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EnabledScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EnabledScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EnabledScope query()
 *
 * @mixin \Eloquent
 */
final class EnabledScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has an is_enabled column
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_enabled')) {
            $builder->where('is_enabled', true);
        }
    }
}
