<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope to filter records by status
 * 
 * This scope automatically applies to models that have status fields
 * and ensures that only records with specific statuses are returned by default.
 */
final class StatusScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has a status column
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'status')) {
            // Define which statuses should be included by default
            $allowedStatuses = $this->getAllowedStatuses($model);
            
            if (!empty($allowedStatuses)) {
                $builder->whereIn('status', $allowedStatuses);
            }
        }
    }

    /**
     * Get allowed statuses for the model
     */
    private function getAllowedStatuses(Model $model): array
    {
        $modelClass = get_class($model);
        
        return match ($modelClass) {
            \App\Models\Order::class => ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed'],
            \App\Models\Campaign::class => ['active', 'running', 'published'],
            \App\Models\Channel::class => ['active', 'enabled'],
            \App\Models\Zone::class => ['active', 'enabled'],
            default => ['active', 'enabled', 'published', 'confirmed', 'completed']
        };
    }
}
