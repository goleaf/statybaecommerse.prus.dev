<?php

declare(strict_types=1);

namespace App\Support\Security\TwoFactor;

/**
 * Minimal TOTP generator to avoid pulling a full dependency tree.
 */
final class TotpGenerator
{
    private const DEFAULT_STEP_SECONDS = 30;
    private const DEFAULT_DIGITS = 6;

    /**
     * Generate a TOTP code for the provided secret at the given timestamp.
     */
    public function generate(string $secret, ?int $timestamp = null, int $digits = self::DEFAULT_DIGITS, int $step = self::DEFAULT_STEP_SECONDS): string
    {
        $binarySecret = $this->base32Decode($secret);

        if ($binarySecret === null) {
            return '';
        }

        $timestamp ??= time();
        $counter = (int) floor($timestamp / $step);

        $counterBytes = pack('N*', 0, $counter);
        $hash = hash_hmac('sha1', $counterBytes, $binarySecret, true);

        $offset = ord(substr($hash, -1)) & 0x0f;
        $binary = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);

        $otp = $binary % (10 ** $digits);

        return str_pad((string) $otp, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Validate a provided TOTP code, allowing a small drift window.
     */
    public function verify(string $secret, string $code, int $window = 1, int $digits = self::DEFAULT_DIGITS, int $step = self::DEFAULT_STEP_SECONDS): bool
    {
        $normalizedCode = preg_replace('/\D/', '', $code);

        if ($normalizedCode === null || $normalizedCode === '') {
            return false;
        }

        $timestamp = time();

        for ($i = -$window; $i <= $window; $i++) {
            $candidate = $this->generate($secret, $timestamp + ($i * $step), $digits, $step);

            if ($candidate !== '' && hash_equals($candidate, $normalizedCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a random Base32 encoded secret suitable for enrolment flows.
     */
    public function generateSecret(int $bytes = 20): string
    {
        $random = random_bytes(max(1, $bytes));

        return $this->base32Encode($random);
    }

    /**
     * Encode binary data to RFC 4648 Base32 without padding.
     */
    private function base32Encode(string $binary): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';

        foreach (unpack('C*', $binary) as $byte) {
            $bits .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
        }

        $chunks = str_split($bits, 5);
        $output = '';

        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0');
            }

            $output .= $alphabet[bindec($chunk)];
        }

        return $output;
    }

    /**
     * Decode an RFC 4648 Base32 string, returning null when the payload is invalid.
     */
    private function base32Decode(string $value): ?string
    {
        $alphabet = array_flip(str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'));
        $sanitized = strtoupper(preg_replace('/[^A-Z2-7]/', '', $value ?? ''));

        if ($sanitized === '') {
            return null;
        }

        $bits = '';

        foreach (str_split($sanitized) as $character) {
            if (! array_key_exists($character, $alphabet)) {
                return null;
            }

            $bits .= str_pad(decbin($alphabet[$character]), 5, '0', STR_PAD_LEFT);
        }

        $bytes = str_split($bits, 8);
        $binary = '';

        foreach ($bytes as $byte) {
            if (strlen($byte) < 8) {
                continue;
            }

            $binary .= chr(bindec($byte));
        }

        return $binary === '' ? null : $binary;
    }
}
