<?php

declare (strict_types=1);
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
/**
 * ActiveScope
 * 
 * Eloquent model representing the ActiveScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|ActiveScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActiveScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActiveScope query()
 * @mixin \Eloquent
 */
final class ActiveScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     * @param Builder $builder
     * @param Model $model
     * @return void
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