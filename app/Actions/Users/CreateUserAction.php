<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\CreateUserData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Single responsibility action for creating users
 */
final readonly class CreateUserAction
{
    public function execute(CreateUserData $userData): User
    {
        return User::create([
            'name'              => $userData->name,
            'email'             => $userData->email,
            'password'          => Hash::make($userData->password),
            'preferred_locale'  => $userData->preferredLocale ?? app()->getLocale(),
            'is_active'         => $userData->isActive ?? true,
            'email_verified_at' => $userData->emailVerified ? now() : null,
            'phone'             => $userData->phone,
            'date_of_birth'     => $userData->dateOfBirth,
        ]);
    }
}
