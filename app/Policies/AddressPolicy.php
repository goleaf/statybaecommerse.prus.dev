<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\Address;
use App\Models\AdminUser;
use App\Models\User;

/**
 * AddressPolicy
 *
 * Authorization policy for AddressPolicy access control with comprehensive permission checking and role-based access.
 */
final class AddressPolicy
{
    /**
     * Handle viewAny functionality with proper error handling.
     */
    public function viewAny(User|AdminUser $user): bool
    {
        return true;
    }

    /**
     * Handle view functionality with proper error handling.
     */
    public function view(User|AdminUser $user, Address $address): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        return $user->id === $address->user_id || ($user->is_admin ?? false);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(User|AdminUser $user): bool
    {
        return true;
    }

    /**
     * Update the specified resource in storage with validation.
     */
    public function update(User|AdminUser $user, Address $address): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        return $user->id === $address->user_id || ($user->is_admin ?? false);
    }

    /**
     * Handle delete functionality with proper error handling.
     */
    public function delete(User|AdminUser $user, Address $address): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        return $user->id === $address->user_id || ($user->is_admin ?? false);
    }

    /**
     * Handle restore functionality with proper error handling.
     */
    public function restore(User|AdminUser $user, Address $address): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        return ($user->is_admin ?? false);
    }

    /**
     * Handle forceDelete functionality with proper error handling.
     */
    public function forceDelete(User|AdminUser $user, Address $address): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        return ($user->is_admin ?? false);
    }
}
