<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\Address;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * AddressPolicy
 *
 * Authorization policy for AddressPolicy access control with comprehensive permission checking and role-based access.
 */
final class AddressPolicy
{
    /**
     * Handle viewAny functionality with proper error handling.
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Handle view functionality with proper error handling.
     * @param User $user
     * @param Address $address
     * @return Response
     */
    public function view(User $user, Address $address): Response
    {
        return ($user->id === $address->user_id || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.address.view_denied'));
    }

    /**
     * Show the form for creating a new resource.
     * @param User $user
     * @return Response
     */
    public function create(User $user): Response
    {
        return Response::allow();
    }

    /**
     * Update the specified resource in storage with validation.
     * @param User $user
     * @param Address $address
     * @return Response
     */
    public function update(User $user, Address $address): Response
    {
        return ($user->id === $address->user_id || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.address.update_denied'));
    }

    /**
     * Handle delete functionality with proper error handling.
     * @param User $user
     * @param Address $address
     * @return Response
     */
    public function delete(User $user, Address $address): Response
    {
        return ($user->id === $address->user_id || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.address.delete_denied'));
    }

    /**
     * Handle restore functionality with proper error handling.
     * @param User $user
     * @param Address $address
     * @return Response
     */
    public function restore(User $user, Address $address): Response
    {
        return $user->is_admin
            ? Response::allow()
            : Response::deny(__('policy.address.restore_denied'));
    }

    /**
     * Handle forceDelete functionality with proper error handling.
     * @param User $user
     * @param Address $address
     * @return Response
     */
    public function forceDelete(User $user, Address $address): Response
    {
        return $user->is_admin
            ? Response::allow()
            : Response::deny(__('policy.address.force_delete_denied'));
    }
}
