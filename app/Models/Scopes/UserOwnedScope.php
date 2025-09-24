<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * UserOwnedScope
 *
 * Eloquent model representing the UserOwnedScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserOwnedScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserOwnedScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserOwnedScope query()
 *
 * @mixin \Eloquent
 */
final class UserOwnedScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Skip scoping for admin users
        if (auth()->check() && (auth()->user()->is_admin ?? false)) {
            return;
        }

        // Check if the model has user-related columns
        $userColumns = $this->getUserColumns($model);
        if (! empty($userColumns) && auth()->check()) {
            $userId = auth()->id();
            if ($userId) {
                $builder->where(function ($query) use ($userColumns, $userId) {
                    foreach ($userColumns as $column) {
                        $query->orWhere($column, $userId);
                    }
                });
            }
        }
    }

    /**
     * Handle getUserColumns functionality with proper error handling.
     */
    private function getUserColumns(Model $model): array
    {
        $table = $model->getTable();
        $schema = $model->getConnection()->getSchemaBuilder();
        $userColumns = [];
        // Check for common user column names
        $possibleColumns = ['user_id', 'created_by', 'owner_id', 'customer_id'];
        foreach ($possibleColumns as $column) {
            if ($schema->hasColumn($table, $column)) {
                $userColumns[] = $column;
            }
        }

        return $userColumns;
    }
}
