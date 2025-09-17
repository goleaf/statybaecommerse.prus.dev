<?php

declare (strict_types=1);
namespace App\Policies;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
/**
 * ReferralCodePolicy
 * 
 * Authorization policy for ReferralCodePolicy access control with comprehensive permission checking and role-based access.
 * 
 */
final class ReferralCodePolicy
{
    use HandlesAuthorization;
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
     * @param ReferralCode $referralCode
     * @return Response
     */
    public function view(User $user, ReferralCode $referralCode): Response
    {
        return ($user->id === $referralCode->user_id || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.referral_code.view_denied'));
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
     * @param ReferralCode $referralCode
     * @return Response
     */
    public function update(User $user, ReferralCode $referralCode): Response
    {
        return ($user->id === $referralCode->user_id || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.referral_code.update_denied'));
    }
    /**
     * Handle delete functionality with proper error handling.
     * @param User $user
     * @param ReferralCode $referralCode
     * @return Response
     */
    public function delete(User $user, ReferralCode $referralCode): Response
    {
        return ($user->id === $referralCode->user_id || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.referral_code.delete_denied'));
    }
    /**
     * Handle restore functionality with proper error handling.
     * @param User $user
     * @param ReferralCode $referralCode
     * @return Response
     */
    public function restore(User $user, ReferralCode $referralCode): Response
    {
        return $user->is_admin
            ? Response::allow()
            : Response::deny(__('policy.referral_code.restore_denied'));
    }
    /**
     * Handle forceDelete functionality with proper error handling.
     * @param User $user
     * @param ReferralCode $referralCode
     * @return Response
     */
    public function forceDelete(User $user, ReferralCode $referralCode): Response
    {
        return $user->is_admin
            ? Response::allow()
            : Response::deny(__('policy.referral_code.force_delete_denied'));
    }
}
