<?php

declare (strict_types=1);
namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
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
     * @return Response
     */
    public function viewAny(AuthUser $authUser): Response
    {
        return $authUser->can('ViewAny:Role')
            ? Response::allow()
            : Response::deny(__('policy.role.view_any_denied'));
    }
    /**
     * Handle view functionality with proper error handling.
     * @param AuthUser $authUser
     * @param Role $role
     * @return Response
     */
    public function view(AuthUser $authUser, Role $role): Response
    {
        return $authUser->can('View:Role')
            ? Response::allow()
            : Response::deny(__('policy.role.view_denied'));
    }
    /**
     * Show the form for creating a new resource.
     * @param AuthUser $authUser
     * @return Response
     */
    public function create(AuthUser $authUser): Response
    {
        return $authUser->can('Create:Role')
            ? Response::allow()
            : Response::deny(__('policy.role.create_denied'));
    }
    /**
     * Update the specified resource in storage with validation.
     * @param AuthUser $authUser
     * @param Role $role
     * @return Response
     */
    public function update(AuthUser $authUser, Role $role): Response
    {
        return $authUser->can('Update:Role')
            ? Response::allow()
            : Response::deny(__('policy.role.update_denied'));
    }
    /**
     * Handle delete functionality with proper error handling.
     * @param AuthUser $authUser
     * @param Role $role
     * @return Response
     */
    public function delete(AuthUser $authUser, Role $role): Response
    {
        return $authUser->can('Delete:Role')
            ? Response::allow()
            : Response::deny(__('policy.role.delete_denied'));
    }
    /**
     * Handle restore functionality with proper error handling.
     * @param AuthUser $authUser
     * @param Role $role
     * @return Response
     */
    public function restore(AuthUser $authUser, Role $role): Response
    {
        return $authUser->can('Restore:Role')
            ? Response::allow()
            : Response::deny(__('policy.role.restore_denied'));
    }
    /**
     * Handle forceDelete functionality with proper error handling.
     * @param AuthUser $authUser
     * @param Role $role
     * @return Response
     */
    public function forceDelete(AuthUser $authUser, Role $role): Response
    {
        return $authUser->can('ForceDelete:Role')
            ? Response::allow()
            : Response::deny(__('policy.role.force_delete_denied'));
    }
    /**
     * Handle forceDeleteAny functionality with proper error handling.
     * @param AuthUser $authUser
     * @return Response
     */
    public function forceDeleteAny(AuthUser $authUser): Response
    {
        return $authUser->can('ForceDeleteAny:Role')
            ? Response::allow()
            : Response::deny(__('policy.role.force_delete_any_denied'));
    }
    /**
     * Handle restoreAny functionality with proper error handling.
     * @param AuthUser $authUser
     * @return Response
     */
    public function restoreAny(AuthUser $authUser): Response
    {
        return $authUser->can('RestoreAny:Role')
            ? Response::allow()
            : Response::deny(__('policy.role.restore_any_denied'));
    }
    /**
     * Handle replicate functionality with proper error handling.
     * @param AuthUser $authUser
     * @param Role $role
     * @return Response
     */
    public function replicate(AuthUser $authUser, Role $role): Response
    {
        return $authUser->can('Replicate:Role')
            ? Response::allow()
            : Response::deny(__('policy.role.replicate_denied'));
    }
    /**
     * Handle reorder functionality with proper error handling.
     * @param AuthUser $authUser
     * @return Response
     */
    public function reorder(AuthUser $authUser): Response
    {
        return $authUser->can('Reorder:Role')
            ? Response::allow()
            : Response::deny(__('policy.role.reorder_denied'));
    }
}