<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * ReferralCodePolicy
 *
 * Authorization policy for ReferralCodePolicy access control with comprehensive permission checking and role-based access.
 */
final class ReferralCodePolicy
{
    use HandlesAuthorization;

    /**
     * Handle viewAny functionality with proper error handling.
     */
    public function viewAny(AuthenticatableContract $user): bool
    {
        return true;
    }

    /**
     * Handle view functionality with proper error handling.
     */
    public function view(AuthenticatableContract $user, ReferralCode $referralCode): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        if ($user instanceof User) {
            return $user->id === $referralCode->user_id || (property_exists($user, 'is_admin') && (bool) $user->is_admin);
        }

        return false;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(AuthenticatableContract $user): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        if ($user instanceof User) {
            return true;
        }

        return false;
    }

    /**
     * Update the specified resource in storage with validation.
     */
    public function update(AuthenticatableContract $user, ReferralCode $referralCode): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        if ($user instanceof User) {
            return $user->id === $referralCode->user_id || (property_exists($user, 'is_admin') && (bool) $user->is_admin);
        }

        return false;
    }

    /**
     * Handle delete functionality with proper error handling.
     */
    public function delete(AuthenticatableContract $user, ReferralCode $referralCode): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        if ($user instanceof User) {
            return $user->id === $referralCode->user_id || (property_exists($user, 'is_admin') && (bool) $user->is_admin);
        }

        return false;
    }

    /**
     * Handle restore functionality with proper error handling.
     */
    public function restore(AuthenticatableContract $user, ReferralCode $referralCode): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        if ($user instanceof User) {
            return property_exists($user, 'is_admin') && (bool) $user->is_admin;
        }

        return false;
    }

    /**
     * Handle forceDelete functionality with proper error handling.
     */
    public function forceDelete(AuthenticatableContract $user, ReferralCode $referralCode): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        if ($user instanceof User) {
            return property_exists($user, 'is_admin') && (bool) $user->is_admin;
        }

        return false;
    }
}
