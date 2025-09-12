<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class ReferralCodePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ReferralCode $referralCode): bool
    {
        return $user->id === $referralCode->user_id || $user->is_admin;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ReferralCode $referralCode): bool
    {
        return $user->id === $referralCode->user_id || $user->is_admin;
    }

    public function delete(User $user, ReferralCode $referralCode): bool
    {
        return $user->id === $referralCode->user_id || $user->is_admin;
    }

    public function restore(User $user, ReferralCode $referralCode): bool
    {
        return $user->is_admin;
    }

    public function forceDelete(User $user, ReferralCode $referralCode): bool
    {
        return $user->is_admin;
    }
}
