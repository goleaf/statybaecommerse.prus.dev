<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\ReferralStatistics;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ReferralService
 *
 * Service class containing ReferralService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class ReferralService
{
    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(private readonly ReferralCodeService $referralCodeService, private readonly ReferralRewardService $referralRewardService) {}

    /**
     * Handle createReferral functionality with proper error handling.
     */
    public function createReferral(int $referrerId, int $referredId, ?string $referralCode = null, array $context = []): ?Referral
    {
        try {
            // Validate users exist
            $referrer = User::findOrFail($referrerId);
            $referred = User::findOrFail($referredId);
            if (config('referral.prevent_self_referral', true) && $referrer->id === $referred->id) {
                throw new Exception('Users cannot refer themselves');
            }
            // Check if user was already referred
            if (Referral::userAlreadyReferred($referredId)) {
                throw new Exception('User has already been referred');
            }
            // Check if referrer can make referrals
            if (! Referral::canUserRefer($referrerId)) {
                throw new Exception('Referrer has reached referral limit');
            }
            // Resolve the referral code so every referral is tied to the
            // referrer's reusable unique code record.
            $code = $this->resolveReferralCode($referrer, $referralCode);
            // Prepare attribution payload with campaign metadata and tracking.
            $attributes = array_merge(
                ['referrer_id' => $referrerId, 'referred_id' => $referredId, 'referral_code' => $code->code, 'status' => 'pending', 'expires_at' => now()->addDays((int) config('referral.referral_expiration_days', 30))],
                $this->extractAttributionContext($context)
            );
            // Create referral
            $referral = Referral::create($attributes);
            // Update statistics
            $this->updateReferralStatistics($referrerId, 'increment_referrals');
            Log::info('Referral created', ['referral_id' => $referral->id, 'referrer_id' => $referrerId, 'referred_id' => $referredId, 'referral_code' => $code->code]);

            return $referral;
        } catch (Exception $e) {
            Log::error('Failed to create referral', ['referrer_id' => $referrerId, 'referred_id' => $referredId, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Handle processReferralCompletion functionality with proper error handling.
     */
    public function processReferralCompletion(int $referredUserId, int $orderId): bool
    {
        try {
            DB::beginTransaction();
            // Find the referral
            $referral = Referral::where('referred_id', $referredUserId)->where('status', 'pending')->first();
            if (! $referral) {
                throw new Exception('No pending referral found for user');
            }
            // Mark referral as completed
            $referral->markAsCompleted();
            // Create rewards
            $this->createReferralRewards($referral, $orderId);
            // Update statistics
            $this->updateReferralStatistics($referral->referrer_id, 'complete_referral');
            DB::commit();
            Log::info('Referral completed', ['referral_id' => $referral->id, 'referrer_id' => $referral->referrer_id, 'referred_id' => $referredUserId, 'order_id' => $orderId]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to process referral completion', ['referred_user_id' => $referredUserId, 'order_id' => $orderId, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return false;
        }
    }

    /**
     * Handle createReferralRewards functionality with proper error handling.
     */
    private function createReferralRewards(Referral $referral, int $orderId): void
    {
        // Create discount for referred user (first order only) using the configured percentage.
        $discountPercentage = (float) config('referral.referred_discount_percentage', 5.0);
        $referredReward = $this->referralRewardService->createReferredDiscount($referral->id, $referral->referred_id, $orderId, $discountPercentage);
        if ($referredReward) {
            $this->updateReferralStatistics($referral->referrer_id, 'add_discount', (float) $referredReward->amount);
        }

        // Promote the referrer into the tiered reward ladder and persist the bonus.
        $tierReward = $this->referralRewardService->createTieredReferrerReward($referral);
        if ($tierReward) {
            $this->updateReferralStatistics($referral->referrer_id, 'add_reward', (float) $tierReward->amount);
        }

        // Maintain backward compatibility with fixed bonus configuration when supplied.
        $referrerBonus = (float) config('referral.referrer_bonus_amount', 0);
        if ($referrerBonus > 0) {
            $manualReward = $this->referralRewardService->createReferrerBonus($referral->id, $referral->referrer_id, $referrerBonus, ['category' => 'credit', 'currency' => config('referral.referrer_bonus_currency', 'EUR')]);
            if ($manualReward) {
                $this->updateReferralStatistics($referral->referrer_id, 'add_reward', (float) $manualReward->amount);
            }
        }
    }

    /**
     * Handle generateReferralCodeForUser functionality with proper error handling.
     */
    public function generateReferralCodeForUser(int $userId): ?ReferralCode
    {
        try {
            $user = User::findOrFail($userId);
            // Check if user already has an active code
            if ($user->hasActiveReferralCode()) {
                return $user->activeReferralCode();
            }
            // Generate new code
            $code = $this->referralCodeService->generateUniqueCode();
            // Create referral code record
            $referralCode = ReferralCode::create(['user_id' => $userId, 'code' => $code, 'is_active' => true, 'expires_at' => now()->addYear()]);
            // Update user's referral code
            $user->update(['referral_code' => $code, 'referral_code_generated_at' => now()]);
            Log::info('Referral code generated for user', ['user_id' => $userId, 'code' => $code]);

            return $referralCode;
        } catch (Exception $e) {
            Log::error('Failed to generate referral code', ['user_id' => $userId, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Handle getUserReferralStats functionality with proper error handling.
     */
    public function getUserReferralStats(int $userId): array
    {
        $user = User::findOrFail($userId);
        $stats = ReferralStatistics::getTotalForUser($userId);
        // Add additional calculated stats
        $stats['conversion_rate'] = $stats['total_referrals'] > 0 ? round($stats['completed_referrals'] / $stats['total_referrals'] * 100, 2) : 0;
        $stats['average_reward'] = $stats['completed_referrals'] > 0 ? round($stats['total_rewards_earned'] / $stats['completed_referrals'], 2) : 0;

        return $stats;
    }

    /**
     * Handle updateReferralStatistics functionality with proper error handling.
     */
    private function updateReferralStatistics(int $userId, string $action, float $value = 0.0): void
    {
        $today = now()->toDateString();
        try {
            $stats = ReferralStatistics::getOrCreateForUserAndDate($userId, $today);
            match ($action) {
                'increment_referrals' => $stats->incrementReferrals(),
                'complete_referral'   => $stats->completeReferral(),
                'add_reward'          => $value > 0 ? $stats->addRewardEarned($value) : null,
                'add_discount'        => $value > 0 ? $stats->addDiscountGiven($value) : null,
            };
        } catch (Exception $e) {
            // Log the error but don't fail the referral completion
            Log::warning('Failed to update referral statistics', ['user_id' => $userId, 'action' => $action, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Resolve a reusable referral code for the given referrer, ensuring we never
     * create orphaned codes and that provided codes belong to the user.
     */
    private function resolveReferralCode(User $referrer, ?string $referralCode): ReferralCode
    {
        if ($referralCode) {
            $code = ReferralCode::findByCode($referralCode);
            if (! $code || $code->user_id !== $referrer->id || ! $code->isValid()) {
                throw new Exception('Invalid referral code provided');
            }

            return $code;
        }

        $generated = $this->generateReferralCodeForUser($referrer->id);
        if (! $generated) {
            throw new Exception('Unable to generate referral code for user');
        }

        return $generated;
    }

    /**
     * Extract attribution metadata from the incoming context payload, limiting
     * persisted fields to the columns supported by the referrals table.
     */
    private function extractAttributionContext(array $context): array
    {
        $allowedKeys = ['source', 'campaign', 'utm_source', 'utm_medium', 'utm_campaign', 'ip_address', 'user_agent'];
        $attributes = [];
        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $context) && $context[$key] !== null) {
                $attributes[$key] = $context[$key];
            }
        }
        if (isset($context['metadata']) && is_array($context['metadata'])) {
            $attributes['metadata'] = $context['metadata'];
        }

        return $attributes;
    }

    /**
     * Handle validateReferralCode functionality with proper error handling.
     */
    public function validateReferralCode(string $code): ?User
    {
        $referralCode = ReferralCode::findByCode($code);
        if (! $referralCode || ! $referralCode->isValid()) {
            return null;
        }

        return $referralCode->user;
    }

    /**
     * Handle getReferralDashboardData functionality with proper error handling.
     */
    public function getReferralDashboardData(int $userId): array
    {
        $user = User::findOrFail($userId);
        $stats = $this->getUserReferralStats($userId);
        $recentReferrals = $user->referrals()->with('referred')->orderBy('created_at', 'desc')->limit(10)->get();
        $pendingRewards = $user->referralRewards()->pending()->orderBy('created_at', 'desc')->get();

        return ['stats' => $stats, 'recent_referrals' => $recentReferrals, 'pending_rewards' => $pendingRewards, 'referral_code' => $user->referral_code, 'referral_url' => $user->referral_url];
    }

    /**
     * Handle cleanupExpiredReferrals functionality with proper error handling.
     */
    public function cleanupExpiredReferrals(): int
    {
        $expiredReferrals = Referral::expired()->get();
        $count = 0;
        foreach ($expiredReferrals as $referral) {
            $referral->markAsExpired();
            $count++;
        }
        Log::info('Cleaned up expired referrals', ['count' => $count]);

        return $count;
    }
}
