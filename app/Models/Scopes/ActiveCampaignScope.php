<?php

declare (strict_types=1);
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
/**
 * ActiveCampaignScope
 * 
 * Eloquent model representing the ActiveCampaignScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|ActiveCampaignScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActiveCampaignScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActiveCampaignScope query()
 * @mixin \Eloquent
 */
final class ActiveCampaignScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has campaign-like date fields
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'starts_at') && $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'ends_at')) {
            $builder->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
        }
    }
}