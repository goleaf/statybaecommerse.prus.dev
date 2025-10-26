<?php

declare(strict_types=1);

namespace App\Support\Security;

use App\Models\User;

/**
 * Value object describing the outcome of a credential verification attempt.
 */
final class LoginAttemptResult
{
    public function __construct(
        private readonly bool $requiresTwoFactor,
        private readonly ?User $user
    ) {
    }

    /**
     * Build a successful result when the user is fully authenticated.
     */
    public static function success(User $user): self
    {
        return new self(false, $user);
    }

    /**
     * Build a result indicating that a two-factor challenge must be completed.
     */
    public static function requiresTwoFactor(User $user): self
    {
        return new self(true, $user);
    }

    /**
     * Determine whether the login flow needs a two-factor challenge step.
     */
    public function requiresTwoFactorChallenge(): bool
    {
        return $this->requiresTwoFactor;
    }

    /**
     * Expose the authenticated user instance when available.
     */
    public function user(): ?User
    {
        return $this->user;
    }
}
