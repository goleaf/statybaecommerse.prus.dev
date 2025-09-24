<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * StatusScope
 *
 * Eloquent model representing the StatusScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|StatusScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusScope query()
 *
 * @mixin \Eloquent
 */
final class StatusScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has a status column
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'status')) {
            // Define which statuses should be included by default
            $allowedStatuses = $this->getAllowedStatuses($model);
            if (! empty($allowedStatuses)) {
                $builder->whereIn('status', $allowedStatuses);
            }
        }
    }

    /**
     * Handle getAllowedStatuses functionality with proper error handling.
     */
    private function getAllowedStatuses(Model $model): array
    {
        $modelClass = get_class($model);

        return match ($modelClass) {
            \App\Models\ProductRequest::class => ['pending', 'in_progress', 'completed', 'cancelled'],
            \App\Models\Order::class => ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed'],
            \App\Models\Campaign::class => ['active', 'running', 'published'],
            \App\Models\Channel::class => ['active', 'enabled'],
            default => ['active', 'enabled', 'published', 'confirmed', 'completed'],
        };
    }
}
