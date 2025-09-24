<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * TenantScope
 *
 * Eloquent model representing the TenantScope entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TenantScope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantScope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TenantScope query()
 *
 * @mixin \Eloquent
 */
final class TenantScope implements Scope
{
    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the model has tenant-related columns
        $tenantColumns = $this->getTenantColumns($model);
        if (! empty($tenantColumns) && $this->hasActiveTenant()) {
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
     * Handle getTenantColumns functionality with proper error handling.
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
     * Handle hasActiveTenant functionality with proper error handling.
     */
    private function hasActiveTenant(): bool
    {
        return auth()->check() || session()->has('tenant_id') || request()->has('tenant_id');
    }

    /**
     * Handle getCurrentTenantId functionality with proper error handling.
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
