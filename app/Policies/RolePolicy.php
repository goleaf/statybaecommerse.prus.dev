<?php

declare (strict_types=1);
namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;
/**
 * RolePolicy
 * 
 * Authorization policy for RolePolicy access control with comprehensive permission checking and role-based access.
 * 
 */
class RolePolicy
{
    use HandlesAuthorization;
    /**
     * Handle viewAny functionality with proper error handling.
     * @param AuthUser $authUser
     * @return bool
     */
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Role');
    }
    /**
     * Handle view functionality with proper error handling.
     * @param AuthUser $authUser
     * @param Role $role
     * @return bool
     */
    public function view(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('View:Role');
    }
    /**
     * Show the form for creating a new resource.
     * @param AuthUser $authUser
     * @return bool
     */
    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Role');
    }
    /**
     * Update the specified resource in storage with validation.
     * @param AuthUser $authUser
     * @param Role $role
     * @return bool
     */
    public function update(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('Update:Role');
    }
    /**
     * Handle delete functionality with proper error handling.
     * @param AuthUser $authUser
     * @param Role $role
     * @return bool
     */
    public function delete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('Delete:Role');
    }
    /**
     * Handle restore functionality with proper error handling.
     * @param AuthUser $authUser
     * @param Role $role
     * @return bool
     */
    public function restore(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('Restore:Role');
    }
    /**
     * Handle forceDelete functionality with proper error handling.
     * @param AuthUser $authUser
     * @param Role $role
     * @return bool
     */
    public function forceDelete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('ForceDelete:Role');
    }
    /**
     * Handle forceDeleteAny functionality with proper error handling.
     * @param AuthUser $authUser
     * @return bool
     */
    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Role');
    }
    /**
     * Handle restoreAny functionality with proper error handling.
     * @param AuthUser $authUser
     * @return bool
     */
    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Role');
    }
    /**
     * Handle replicate functionality with proper error handling.
     * @param AuthUser $authUser
     * @param Role $role
     * @return bool
     */
    public function replicate(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('Replicate:Role');
    }
    /**
     * Handle reorder functionality with proper error handling.
     * @param AuthUser $authUser
     * @return bool
     */
    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Role');
    }
}