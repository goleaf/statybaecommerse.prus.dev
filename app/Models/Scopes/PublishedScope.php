<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * PublishedScope
 *
 * Eloquent model representing the PublishedScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PublishedScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PublishedScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PublishedScope query()
 *
 * @mixin \Eloquent
 */
final class PublishedScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has a published_at column
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'published_at')) {
            $builder->whereNotNull('published_at')->where('published_at', '<=', now());
        }
    }
}
