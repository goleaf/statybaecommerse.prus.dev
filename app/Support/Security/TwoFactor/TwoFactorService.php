<?php

declare(strict_types=1);

namespace App\Support\Security\TwoFactor;

use App\Models\User;

/**
 * Coordinate two-factor verification checks and recovery code rotation.
 */
final class TwoFactorService
{
    public function __construct(private readonly TotpGenerator $generator)
    {
    }

    /**
     * Verify a submitted code against the user's configured second factor.
     */
    public function verify(User $user, string $code): bool
    {
        if ($this->verifyTotp($user, $code)) {
            return true;
        }

        return $this->attemptRecoveryCode($user, $code);
    }

    /**
     * Validate a TOTP code against the stored shared secret.
     */
    public function verifyTotp(User $user, string $code): bool
    {
        $secret = (string) $user->two_factor_secret;
        $normalized = $this->normalizeTotpCode($code);

        if ($secret === '' || $normalized === '') {
            return false;
        }

        return $this->generator->verify($secret, $normalized);
    }

    /**
     * Attempt to consume a recovery code for the user.
     */
    public function attemptRecoveryCode(User $user, string $code): bool
    {
        $normalized = $this->normalizeRecoveryCode($code);
        $codes = $user->two_factor_recovery_codes;

        if ($normalized === '' || ! is_array($codes)) {
            return false;
        }

        $hashedInput = $this->hashRecoveryCode($normalized);

        foreach ($codes as $index => $stored) {
            if (! is_string($stored)) {
                continue;
            }

            if (hash_equals($stored, $hashedInput)) {
                $remaining = $codes;
                unset($remaining[$index]);

                $user->forceFill([
                    'two_factor_recovery_codes' => array_values($remaining),
                ]);
                $user->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Hash raw recovery codes so we never persist them in plain text.
     */
    public function hashRecoveryCode(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    /**
     * Prepare hashed recovery codes for storage while exposing the plain variants to the caller.
     */
    public function hashRecoveryCodes(array $codes): array
    {
        $hashed = [];

        foreach ($codes as $code) {
            if (! is_string($code) || $code === '') {
                continue;
            }

            $hashed[] = $this->hashRecoveryCode($this->normalizeRecoveryCode($code));
        }

        return $hashed;
    }

    /**
     * Generate a Base32 encoded shared secret for two-factor enrolment.
     */
    public function generateSecret(): string
    {
        return $this->generator->generateSecret();
    }

    /**
     * Normalize authenticator codes by stripping whitespace and non-digits.
     */
    private function normalizeTotpCode(string $code): string
    {
        return preg_replace('/\D/', '', $code) ?? '';
    }

    /**
     * Normalize recovery codes by uppercasing and removing separators.
     */
    private function normalizeRecoveryCode(string $code): string
    {
        $normalized = preg_replace('/[^A-Z0-9]/i', '', $code);

        return $normalized === null ? '' : strtoupper($normalized);
    }
}
