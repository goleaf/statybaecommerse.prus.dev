<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Referral;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * ReferralPolicy
 *
 * Authorization policy for ReferralPolicy access control with comprehensive permission checking and role-based access.
 */
final class ReferralPolicy
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
    public function view(AuthenticatableContract $user, Referral $referral): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        return ($user->id === $referral->referrer_id) || ($user->id === $referral->referred_id) || ((bool) ($user->is_admin ?? false));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(AuthenticatableContract $user): bool
    {
        return true;
    }

    /**
     * Update the specified resource in storage with validation.
     */
    public function update(AuthenticatableContract $user, Referral $referral): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        return ($user->id === $referral->referrer_id) || ((bool) ($user->is_admin ?? false));
    }

    /**
     * Handle delete functionality with proper error handling.
     */
    public function delete(AuthenticatableContract $user, Referral $referral): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        return ($user->id === $referral->referrer_id) || ((bool) ($user->is_admin ?? false));
    }

    /**
     * Handle restore functionality with proper error handling.
     */
    public function restore(AuthenticatableContract $user, Referral $referral): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        return (bool) ($user->is_admin ?? false);
    }

    /**
     * Handle forceDelete functionality with proper error handling.
     */
    public function forceDelete(AuthenticatableContract $user, Referral $referral): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        return (bool) ($user->is_admin ?? false);
    }
}
