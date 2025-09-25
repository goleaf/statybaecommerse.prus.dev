<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Spatie\Permission\Models\Role;

/**
 * RolePolicy
 *
 * Authorization policy for RolePolicy access control with comprehensive permission checking and role-based access.
 */
class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Handle viewAny functionality with proper error handling.
     */
    public function viewAny(AuthenticatableContract $authUser): bool
    {
        return $authUser->can('ViewAny:Role');
    }

    /**
     * Handle view functionality with proper error handling.
     */
    public function view(AuthenticatableContract $authUser, Role $role): bool
    {
        return $authUser->can('View:Role');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(AuthenticatableContract $authUser): bool
    {
        return $authUser->can('Create:Role');
    }

    /**
     * Update the specified resource in storage with validation.
     */
    public function update(AuthenticatableContract $authUser, Role $role): bool
    {
        return $authUser->can('Update:Role');
    }

    /**
     * Handle delete functionality with proper error handling.
     */
    public function delete(AuthenticatableContract $authUser, Role $role): bool
    {
        return $authUser->can('Delete:Role');
    }

    /**
     * Handle restore functionality with proper error handling.
     */
    public function restore(AuthenticatableContract $authUser, Role $role): bool
    {
        return $authUser->can('Restore:Role');
    }

    /**
     * Handle forceDelete functionality with proper error handling.
     */
    public function forceDelete(AuthenticatableContract $authUser, Role $role): bool
    {
        return $authUser->can('ForceDelete:Role');
    }

    /**
     * Handle forceDeleteAny functionality with proper error handling.
     */
    public function forceDeleteAny(AuthenticatableContract $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Role');
    }

    /**
     * Handle restoreAny functionality with proper error handling.
     */
    public function restoreAny(AuthenticatableContract $authUser): bool
    {
        return $authUser->can('RestoreAny:Role');
    }

    /**
     * Handle replicate functionality with proper error handling.
     */
    public function replicate(AuthenticatableContract $authUser, Role $role): bool
    {
        return $authUser->can('Replicate:Role');
    }

    /**
     * Handle reorder functionality with proper error handling.
     */
    public function reorder(AuthenticatableContract $authUser): bool
    {
        return $authUser->can('Reorder:Role');
    }
}
