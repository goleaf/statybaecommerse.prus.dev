<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\UpdateUserProfileData;
use App\Models\User;

/**
 * Single responsibility action for updating user profiles
 */
final readonly class UpdateUserProfileAction
{
    public function execute(User $user, UpdateUserProfileData $profileData): User
    {
        $user->update([
            'name'             => $profileData->name ?? $user->name,
            'email'            => $profileData->email ?? $user->email,
            'phone'            => $profileData->phone ?? $user->phone,
            'date_of_birth'    => $profileData->dateOfBirth ?? $user->date_of_birth,
            'preferred_locale' => $profileData->preferredLocale ?? $user->preferred_locale,
        ]);

        return $user->fresh();
    }
}
