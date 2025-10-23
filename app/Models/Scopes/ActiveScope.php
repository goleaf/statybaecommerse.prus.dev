<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * ActiveScope
 *
 * Eloquent model representing the ActiveScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
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
        if ($model instanceof Order) {
            // Orders use dedicated status scopes; avoid duplicating constraints that hide seeded records.
            return;
        }

        // Prefer stricter check when both flags exist
        $schema = $model->getConnection()->getSchemaBuilder();
        $table = $model->getTable();

        // Guard against running scope logic before migrations have created the table.
        if (! $schema->hasTable($table)) {
            return;
        }

        // Prefer stricter check when both flags exist to keep behaviour predictable.
        $hasIsActive = $schema->hasColumn($table, 'is_active');
        $hasIsVisible = $schema->hasColumn($table, 'is_visible');
        $hasIsEnabled = $schema->hasColumn($table, 'is_enabled');
        $hasStatus = $schema->hasColumn($table, 'status');

        if ($hasIsActive && $hasIsVisible) {
            $builder->where('is_active', true)->where('is_visible', true);

            return;
        }

        if ($hasIsActive) {
            $builder->where('is_active', true);

            return;
        }

        if ($hasIsEnabled) {
            $builder->where('is_enabled', true);

            return;
        }

        if ($hasIsVisible) {
            $builder->where('is_visible', true);
        } elseif ($schema->hasColumn($table, 'status')) {
            // Defer to model-specific allowlists instead of defaulting to a hard-coded
            // "active" value so enums such as orders remain queryable in diagnostics.
            $defaultStatuses = $this->getDefaultStatuses($model);

            if ($defaultStatuses !== []) {
                $builder->whereIn('status', $defaultStatuses);
            }
        }
    }

    /**
     * Surface default status filters on a per-model basis to avoid applying
     * incompatible hard-coded values (e.g. the orders enum has no "active").
     *
     * @return array<int, string>
     */
    private function getDefaultStatuses(Model $model): array
    {
        return match ($model::class) {
            \App\Models\Order::class => [],
            default                  => ['active'],
        };
    }
}
