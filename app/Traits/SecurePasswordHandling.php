<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

/**
 * Secure password handling trait for models.
 */
trait SecurePasswordHandling
{
    /**
     * Securely set password with validation.
     */
    public function setPasswordAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        // Validate password strength
        if (! $this->isPasswordSecure($value)) {
            throw new InvalidArgumentException('Password does not meet security requirements');
        }

        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Validate password security requirements.
     */
    private function isPasswordSecure(string $password): bool
    {
        // Minimum 8 characters
        if (strlen($password) < 8) {
            return false;
        }

        // Must contain at least one uppercase letter
        if (! preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Must contain at least one lowercase letter
        if (! preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Must contain at least one number
        if (! preg_match('/[0-9]/', $password)) {
            return false;
        }

        // Must contain at least one special character
        if (! preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Securely update password with current password verification.
     */
    public function updatePassword(string $currentPassword, string $newPassword): bool
    {
        if (! Hash::check($currentPassword, $this->password)) {
            return false;
        }

        $this->password = $newPassword;

        return $this->save();
    }

    /**
     * Check if password needs to be rehashed.
     */
    public function needsPasswordRehash(): bool
    {
        return Hash::needsRehash($this->password);
    }

    /**
     * Rehash password if needed.
     */
    public function rehashPasswordIfNeeded(): bool
    {
        if (! $this->needsPasswordRehash()) {
            return false;
        }

        // This requires the plain password, which we don't have
        // This method should be called during authentication when we have the plain password
        return false;
    }
}
