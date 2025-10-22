<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * ActiveScope
 *
 * Global scope that keeps model queries limited to active or visible records when supported by the table schema.
 */
final class ActiveScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(Builder $builder, Model $model): void
    {
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

            return;
        }

        if ($hasStatus) {
            $builder->where('status', 'active');
        }
    }
}
