<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * ApprovedScope
 *
 * Eloquent model representing the ApprovedScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovedScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovedScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovedScope query()
 *
 * @mixin \Eloquent
 */
final class ApprovedScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has an is_approved column
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_approved')) {
            $builder->where('is_approved', true);
        }
    }
}
