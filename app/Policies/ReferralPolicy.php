<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Handle view functionality with proper error handling.
     */
    public function view(User $user, Referral $referral): bool
    {
        return $user->id === $referral->referrer_id || $user->id === $referral->referred_id || $user->is_admin;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Update the specified resource in storage with validation.
     */
    public function update(User $user, Referral $referral): bool
    {
        return $user->id === $referral->referrer_id || $user->is_admin;
    }

    /**
     * Handle delete functionality with proper error handling.
     */
    public function delete(User $user, Referral $referral): bool
    {
        return $user->id === $referral->referrer_id || $user->is_admin;
    }

    /**
     * Handle restore functionality with proper error handling.
     */
    public function restore(User $user, Referral $referral): bool
    {
        return $user->is_admin;
    }

    /**
     * Handle forceDelete functionality with proper error handling.
     */
    public function forceDelete(User $user, Referral $referral): bool
    {
        return $user->is_admin;
    }
}
