<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope to filter only active campaigns
 * 
 * This scope automatically applies to models that have campaign-like date fields
 * and ensures that only active campaigns are returned by default.
 */
final class ActiveCampaignScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has campaign-like date fields
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'starts_at') &&
            $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'ends_at')) {
            
            $builder->where(function ($query) {
                $query->whereNull('starts_at')
                      ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>=', now());
            });
        }
    }
}
