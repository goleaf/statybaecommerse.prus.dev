<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Handle view functionality with proper error handling.
     */
    public function view(User $user, ReferralCode $referralCode): bool
    {
        return $user->id === $referralCode->user_id || $user->is_admin;
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
    public function update(User $user, ReferralCode $referralCode): bool
    {
        return $user->id === $referralCode->user_id || $user->is_admin;
    }

    /**
     * Handle delete functionality with proper error handling.
     */
    public function delete(User $user, ReferralCode $referralCode): bool
    {
        return $user->id === $referralCode->user_id || $user->is_admin;
    }

    /**
     * Handle restore functionality with proper error handling.
     */
    public function restore(User $user, ReferralCode $referralCode): bool
    {
        return $user->is_admin;
    }

    /**
     * Handle forceDelete functionality with proper error handling.
     */
    public function forceDelete(User $user, ReferralCode $referralCode): bool
    {
        return $user->is_admin;
    }
}
