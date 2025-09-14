<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope for user-owned data filtering
 * 
 * This scope automatically applies user filtering to models that have user_id
 * or similar user ownership fields, ensuring users only see their own data.
 */
final class UserOwnedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has user-related columns
        $userColumns = $this->getUserColumns($model);
        
        if (!empty($userColumns) && auth()->check()) {
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
     * Get user-related columns for the model
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
