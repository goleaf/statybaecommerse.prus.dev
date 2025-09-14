<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope for multi-tenant data isolation
 * 
 * This scope automatically applies tenant filtering to models that have tenant_id
 * or similar tenant identification fields, ensuring data isolation between tenants.
 */
final class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has tenant-related columns
        $tenantColumns = $this->getTenantColumns($model);
        
        if (!empty($tenantColumns) && $this->hasActiveTenant()) {
            $tenantId = $this->getCurrentTenantId();
            
            if ($tenantId) {
                $builder->where(function ($query) use ($tenantColumns, $tenantId) {
                    foreach ($tenantColumns as $column) {
                        $query->orWhere($column, $tenantId);
                    }
                });
            }
        }
    }

    /**
     * Get tenant-related columns for the model
     */
    private function getTenantColumns(Model $model): array
    {
        $table = $model->getTable();
        $schema = $model->getConnection()->getSchemaBuilder();
        
        $tenantColumns = [];
        
        // Check for common tenant column names
        $possibleColumns = ['tenant_id', 'company_id', 'organization_id', 'workspace_id'];
        
        foreach ($possibleColumns as $column) {
            if ($schema->hasColumn($table, $column)) {
                $tenantColumns[] = $column;
            }
        }
        
        return $tenantColumns;
    }

    /**
     * Check if there's an active tenant context
     */
    private function hasActiveTenant(): bool
    {
        return auth()->check() || session()->has('tenant_id') || request()->has('tenant_id');
    }

    /**
     * Get the current tenant ID
     */
    private function getCurrentTenantId(): ?int
    {
        // Try to get tenant ID from authenticated user
        if (auth()->check() && auth()->user()->tenant_id) {
            return auth()->user()->tenant_id;
        }
        
        // Try to get from session
        if (session()->has('tenant_id')) {
            return session('tenant_id');
        }
        
        // Try to get from request
        if (request()->has('tenant_id')) {
            return (int) request('tenant_id');
        }
        
        return null;
    }
}
