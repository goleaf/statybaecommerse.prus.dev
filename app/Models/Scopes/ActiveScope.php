<?php

declare(strict_types=1);

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
 *
 * @mixin \Eloquent
 */
final class ActiveScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Prefer stricter check when both flags exist
        $schema = $model->getConnection()->getSchemaBuilder();
        $table = $model->getTable();
        $hasIsActive = $schema->hasColumn($table, 'is_active');
        $hasIsVisible = $schema->hasColumn($table, 'is_visible');

        if ($hasIsActive && $hasIsVisible) {
            $builder->where('is_active', true)->where('is_visible', true);
        } elseif ($hasIsActive) {
            $builder->where('is_active', true);
        } elseif ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_enabled')) {
            $builder->where('is_enabled', true);
        } elseif ($hasIsVisible) {
            $builder->where('is_visible', true);
        } elseif ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'status')) {
            $builder->where('status', 'active');
        }
    }
}
