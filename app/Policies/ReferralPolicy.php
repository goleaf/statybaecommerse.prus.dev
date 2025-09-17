<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

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
     * @param Referral $referral
     * @return Response
     */
    public function view(User $user, Referral $referral): Response
    {
        return ($user->id === $referral->referrer_id || $user->id === $referral->referred_id || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.referral.view_denied'));
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
     * @param Referral $referral
     * @return Response
     */
    public function update(User $user, Referral $referral): Response
    {
        return ($user->id === $referral->referrer_id || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.referral.update_denied'));
    }

    /**
     * Handle delete functionality with proper error handling.
     * @param User $user
     * @param Referral $referral
     * @return Response
     */
    public function delete(User $user, Referral $referral): Response
    {
        return ($user->id === $referral->referrer_id || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.referral.delete_denied'));
    }

    /**
     * Handle restore functionality with proper error handling.
     * @param User $user
     * @param Referral $referral
     * @return Response
     */
    public function restore(User $user, Referral $referral): Response
    {
        return $user->is_admin
            ? Response::allow()
            : Response::deny(__('policy.referral.restore_denied'));
    }

    /**
     * Handle forceDelete functionality with proper error handling.
     * @param User $user
     * @param Referral $referral
     * @return Response
     */
    public function forceDelete(User $user, Referral $referral): Response
    {
        return $user->is_admin
            ? Response::allow()
            : Response::deny(__('policy.referral.force_delete_denied'));
    }
}
