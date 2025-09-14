<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ReferralCode;
use Illuminate\Support\Str;

final /**
 * ReferralCodeService
 * 
 * Service class containing business logic and external integrations.
 */
class ReferralCodeService
{
    /**
     * Generate a unique referral code
     */
    public function generateUniqueCode(): string
    {
        $code = $this->generateCode();
        
        ReferralCode::where('code', $code)
            ->existsOr(function () use (&$code) {
                $code = $this->generateUniqueCode();
            });

        return $code;
    }

    /**
     * Generate a referral code using different strategies
     */
    private function generateCode(): string
    {
        $strategy = config('referral.code_generation_strategy', 'mixed');

        return match ($strategy) {
            'alphanumeric' => $this->generateAlphanumericCode(),
            'numeric' => $this->generateNumericCode(),
            'mixed' => $this->generateMixedCode(),
            default => $this->generateMixedCode(),
        };
    }

    /**
     * Generate alphanumeric code (letters and numbers)
     */
    private function generateAlphanumericCode(): string
    {
        $length = config('referral.code_length', 8);

        return strtoupper(Str::random($length));
    }

    /**
     * Generate numeric code (numbers only)
     */
    private function generateNumericCode(): string
    {
        $length = config('referral.code_length', 8);
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;

        return (string) rand($min, $max);
    }

    /**
     * Generate mixed code (combination of letters, numbers, and special chars)
     */
    private function generateMixedCode(): string
    {
        $length = config('referral.code_length', 8);
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $code;
    }

    /**
     * Validate referral code format
     */
    public function validateCodeFormat(string $code): bool
    {
        $minLength = config('referral.code_min_length', 6);
        $maxLength = config('referral.code_max_length', 12);
        $pattern = config('referral.code_pattern', '/^[A-Z0-9]+$/');

        if (strlen($code) < $minLength || strlen($code) > $maxLength) {
            return false;
        }

        return (bool) preg_match($pattern, $code);
    }

    /**
     * Check if code is available (not already used)
     */
    public function isCodeAvailable(string $code): bool
    {
        return ! ReferralCode::where('code', $code)->exists();
    }

    /**
     * Create a custom referral code for user
     */
    public function createCustomCode(int $userId, string $code): ?ReferralCode
    {
        // Validate format
        if (! $this->validateCodeFormat($code)) {
            throw new \InvalidArgumentException('Invalid referral code format');
        }

        // Check availability
        if (! $this->isCodeAvailable($code)) {
            throw new \InvalidArgumentException('Referral code already exists');
        }

        // Create the code
        return ReferralCode::create([
            'user_id' => $userId,
            'code' => strtoupper($code),
            'is_active' => true,
            'expires_at' => now()->addYear(),
        ]);
    }

    /**
     * Deactivate a referral code
     */
    public function deactivateCode(string $code): bool
    {
        $referralCode = ReferralCode::findByCode($code);

        if (! $referralCode) {
            return false;
        }

        $referralCode->deactivate();

        return true;
    }

    /**
     * Get referral URL for a code
     */
    public function getReferralUrl(string $code): string
    {
        $baseUrl = config('app.url');
        $referralPath = config('referral.registration_path', '/register');

        return $baseUrl.$referralPath.'?ref='.$code;
    }

    /**
     * Extract referral code from URL
     */
    public function extractCodeFromUrl(string $url): ?string
    {
        $parsedUrl = parse_url($url);

        if (! isset($parsedUrl['query'])) {
            return null;
        }

        parse_str($parsedUrl['query'], $queryParams);

        return $queryParams['ref'] ?? null;
    }

    /**
     * Get statistics for a referral code
     */
    public function getCodeStatistics(string $code): array
    {
        $referralCode = ReferralCode::findByCode($code);

        if (! $referralCode) {
            return [];
        }

        $totalReferrals = $referralCode->user->referrals()->count();
        $completedReferrals = $referralCode->user->referrals()->completed()->count();
        $pendingReferrals = $referralCode->user->referrals()->active()->count();

        return [
            'code' => $code,
            'user_id' => $referralCode->user_id,
            'is_active' => $referralCode->is_active,
            'expires_at' => $referralCode->expires_at,
            'total_referrals' => $totalReferrals,
            'completed_referrals' => $completedReferrals,
            'pending_referrals' => $pendingReferrals,
            'conversion_rate' => $totalReferrals > 0 ? round(($completedReferrals / $totalReferrals) * 100, 2) : 0,
        ];
    }
}
