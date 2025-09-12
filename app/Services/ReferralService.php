<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\ReferralReward;
use App\Models\ReferralStatistics;
use App\Models\Discount;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ReferralService
{
    public function __construct(
        private readonly ReferralCodeService $referralCodeService,
        private readonly ReferralRewardService $referralRewardService
    ) {}

    /**
     * Create a referral relationship between two users
     */
    public function createReferral(int $referrerId, int $referredId, ?string $referralCode = null): ?Referral
    {
        try {
            // Validate users exist
            $referrer = User::findOrFail($referrerId);
            $referred = User::findOrFail($referredId);

            // Check if user was already referred
            if (Referral::userAlreadyReferred($referredId)) {
                throw new \Exception('User has already been referred');
            }

            // Check if referrer can make referrals
            if (!Referral::canUserRefer($referrerId)) {
                throw new \Exception('Referrer has reached referral limit');
            }

            // Generate referral code if not provided
            if (!$referralCode) {
                $referralCode = $this->referralCodeService->generateUniqueCode();
            }

            // Create referral
            $referral = Referral::create([
                'referrer_id' => $referrerId,
                'referred_id' => $referredId,
                'referral_code' => $referralCode,
                'status' => 'pending',
                'expires_at' => now()->addDays(30), // 30 days to complete
            ]);

            // Update statistics
            $this->updateReferralStatistics($referrerId, 'increment_referrals');

            Log::info('Referral created', [
                'referral_id' => $referral->id,
                'referrer_id' => $referrerId,
                'referred_id' => $referredId,
                'referral_code' => $referralCode,
            ]);

            return $referral;
        } catch (\Exception $e) {
            Log::error('Failed to create referral', [
                'referrer_id' => $referrerId,
                'referred_id' => $referredId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Process referral when referred user makes their first order
     */
    public function processReferralCompletion(int $referredUserId, int $orderId): bool
    {
        try {
            DB::beginTransaction();

            // Find the referral
            $referral = Referral::where('referred_id', $referredUserId)
                ->where('status', 'pending')
                ->first();

            if (!$referral) {
                throw new \Exception('No pending referral found for user');
            }

            // Mark referral as completed
            $referral->markAsCompleted();

            // Create rewards
            $this->createReferralRewards($referral, $orderId);

            // Update statistics
            $this->updateReferralStatistics($referral->referrer_id, 'complete_referral');

            DB::commit();

            Log::info('Referral completed', [
                'referral_id' => $referral->id,
                'referrer_id' => $referral->referrer_id,
                'referred_id' => $referredUserId,
                'order_id' => $orderId,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to process referral completion', [
                'referred_user_id' => $referredUserId,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create referral rewards for both referrer and referred user
     */
    private function createReferralRewards(Referral $referral, int $orderId): void
    {
        // Create 5% discount for referred user (first order only)
        $this->referralRewardService->createReferredDiscount(
            $referral->id,
            $referral->referred_id,
            $orderId,
            5.0 // 5% discount
        );

        // Create bonus for referrer (optional - can be configured)
        $referrerBonus = config('referral.referrer_bonus_amount', 0);
        if ($referrerBonus > 0) {
            $this->referralRewardService->createReferrerBonus(
                $referral->id,
                $referral->referrer_id,
                $referrerBonus
            );
        }
    }

    /**
     * Generate referral code for user
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
            $referralCode = ReferralCode::create([
                'user_id' => $userId,
                'code' => $code,
                'is_active' => true,
                'expires_at' => now()->addYear(), // Valid for 1 year
            ]);

            // Update user's referral code
            $user->update([
                'referral_code' => $code,
                'referral_code_generated_at' => now(),
            ]);

            Log::info('Referral code generated for user', [
                'user_id' => $userId,
                'code' => $code,
            ]);

            return $referralCode;
        } catch (\Exception $e) {
            Log::error('Failed to generate referral code', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get referral statistics for a user
     */
    public function getUserReferralStats(int $userId): array
    {
        $user = User::findOrFail($userId);

        $stats = ReferralStatistics::getTotalForUser($userId);

        // Add additional calculated stats
        $stats['conversion_rate'] = $stats['total_referrals'] > 0 
            ? round(($stats['completed_referrals'] / $stats['total_referrals']) * 100, 2)
            : 0;

        $stats['average_reward'] = $stats['completed_referrals'] > 0
            ? round($stats['total_rewards_earned'] / $stats['completed_referrals'], 2)
            : 0;

        return $stats;
    }

    /**
     * Update referral statistics
     */
    private function updateReferralStatistics(int $userId, string $action): void
    {
        $today = now()->toDateString();
        $stats = ReferralStatistics::getOrCreateForUserAndDate($userId, $today);

        match ($action) {
            'increment_referrals' => $stats->incrementReferrals(),
            'complete_referral' => $stats->completeReferral(),
            'add_reward' => $stats->addRewardEarned(0), // Amount will be set separately
            'add_discount' => $stats->addDiscountGiven(0), // Amount will be set separately
        };
    }

    /**
     * Validate referral code and get referrer
     */
    public function validateReferralCode(string $code): ?User
    {
        $referralCode = ReferralCode::findByCode($code);

        if (!$referralCode || !$referralCode->isValid()) {
            return null;
        }

        return $referralCode->user;
    }

    /**
     * Get referral dashboard data for user
     */
    public function getReferralDashboardData(int $userId): array
    {
        $user = User::findOrFail($userId);

        $stats = $this->getUserReferralStats($userId);

        $recentReferrals = $user->referrals()
            ->with('referred')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $pendingRewards = $user->referralRewards()
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'stats' => $stats,
            'recent_referrals' => $recentReferrals,
            'pending_rewards' => $pendingRewards,
            'referral_code' => $user->referral_code,
            'referral_url' => $user->referral_url,
        ];
    }

    /**
     * Clean up expired referrals
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

