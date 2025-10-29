<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Apply automatic user scoping for models that expose owner-style columns.
 */
final class UserOwnedScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Bail out early when the connection resolver has not been bootstrapped yet;
        // Pest unit tests occasionally exercise the model outside a full Laravel
        // TestCase context, and attempting to touch the schema builder would throw.
        if ($model::getConnectionResolver() === null) {
            return;
        }

        // Skip scoping when the application is running in the console so seeders,
        // artisan commands, and automated tests continue to operate on the full
        // dataset without requiring an authenticated context.
        if (app()->runningInConsole()) {
            return;
        }

        // Skip scoping for admin or users with super_admin role
        if (auth()->check() && ((auth()->user()->is_admin ?? false) || auth()->user()?->hasRole('super_admin'))) {
            return;
        }

        // Check if the model has user-related columns
        $userColumns = $this->getUserColumns($model);
        if ($userColumns !== []) {
            $userId = auth()->id();

            if (! $userId) {
                return;
            }

            $builder->where(function (Builder $query) use ($userColumns, $userId): void {
                foreach ($userColumns as $column) {
                    $query->orWhere($column, $userId);
                }
            });
        }
    }

    /**
     * Handle getUserColumns functionality with proper error handling.
     *
     * @return list<string>
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
