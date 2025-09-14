<?php

declare (strict_types=1);
namespace App\Services;

use App\Models\ReferralCode;
use Illuminate\Support\Str;
/**
 * ReferralCodeService
 * 
 * Service class containing ReferralCodeService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class ReferralCodeService
{
    /**
     * Handle generateUniqueCode functionality with proper error handling.
     * @return string
     */
    public function generateUniqueCode(): string
    {
        $code = $this->generateCode();
        ReferralCode::where('code', $code)->existsOr(function () use (&$code) {
            $code = $this->generateUniqueCode();
        });
        return $code;
    }
    /**
     * Handle generateCode functionality with proper error handling.
     * @return string
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
     * Handle generateAlphanumericCode functionality with proper error handling.
     * @return string
     */
    private function generateAlphanumericCode(): string
    {
        $length = config('referral.code_length', 8);
        return strtoupper(Str::random($length));
    }
    /**
     * Handle generateNumericCode functionality with proper error handling.
     * @return string
     */
    private function generateNumericCode(): string
    {
        $length = config('referral.code_length', 8);
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;
        return (string) rand($min, $max);
    }
    /**
     * Handle generateMixedCode functionality with proper error handling.
     * @return string
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
     * Handle validateCodeFormat functionality with proper error handling.
     * @param string $code
     * @return bool
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
     * Handle isCodeAvailable functionality with proper error handling.
     * @param string $code
     * @return bool
     */
    public function isCodeAvailable(string $code): bool
    {
        return !ReferralCode::where('code', $code)->exists();
    }
    /**
     * Handle createCustomCode functionality with proper error handling.
     * @param int $userId
     * @param string $code
     * @return ReferralCode|null
     */
    public function createCustomCode(int $userId, string $code): ?ReferralCode
    {
        // Validate format
        if (!$this->validateCodeFormat($code)) {
            throw new \InvalidArgumentException('Invalid referral code format');
        }
        // Check availability
        if (!$this->isCodeAvailable($code)) {
            throw new \InvalidArgumentException('Referral code already exists');
        }
        // Create the code
        return ReferralCode::create(['user_id' => $userId, 'code' => strtoupper($code), 'is_active' => true, 'expires_at' => now()->addYear()]);
    }
    /**
     * Handle deactivateCode functionality with proper error handling.
     * @param string $code
     * @return bool
     */
    public function deactivateCode(string $code): bool
    {
        $referralCode = ReferralCode::findByCode($code);
        if (!$referralCode) {
            return false;
        }
        $referralCode->deactivate();
        return true;
    }
    /**
     * Handle getReferralUrl functionality with proper error handling.
     * @param string $code
     * @return string
     */
    public function getReferralUrl(string $code): string
    {
        $baseUrl = config('app.url');
        $referralPath = config('referral.registration_path', '/register');
        return $baseUrl . $referralPath . '?ref=' . $code;
    }
    /**
     * Handle extractCodeFromUrl functionality with proper error handling.
     * @param string $url
     * @return string|null
     */
    public function extractCodeFromUrl(string $url): ?string
    {
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['query'])) {
            return null;
        }
        parse_str($parsedUrl['query'], $queryParams);
        return $queryParams['ref'] ?? null;
    }
    /**
     * Handle getCodeStatistics functionality with proper error handling.
     * @param string $code
     * @return array
     */
    public function getCodeStatistics(string $code): array
    {
        $referralCode = ReferralCode::findByCode($code);
        if (!$referralCode) {
            return [];
        }
        $totalReferrals = $referralCode->user->referrals()->count();
        $completedReferrals = $referralCode->user->referrals()->completed()->count();
        $pendingReferrals = $referralCode->user->referrals()->active()->count();
        return ['code' => $code, 'user_id' => $referralCode->user_id, 'is_active' => $referralCode->is_active, 'expires_at' => $referralCode->expires_at, 'total_referrals' => $totalReferrals, 'completed_referrals' => $completedReferrals, 'pending_referrals' => $pendingReferrals, 'conversion_rate' => $totalReferrals > 0 ? round($completedReferrals / $totalReferrals * 100, 2) : 0];
    }
}