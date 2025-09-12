<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralReward;
use Illuminate\Support\Facades\Log;

final class ReferralRewardService
{
    /**
     * Create a discount reward for the referred user
     */
    public function createReferredDiscount(int $referralId, int $userId, int $orderId, float $percentage): ?ReferralReward
    {
        try {
            // Create the discount in the system
            $discount = $this->createReferralDiscount($userId, $percentage);

            if (! $discount) {
                throw new \Exception('Failed to create referral discount');
            }

            // Create the reward record
            $reward = ReferralReward::create([
                'referral_id' => $referralId,
                'user_id' => $userId,
                'order_id' => $orderId,
                'type' => 'referred_discount',
                'amount' => $percentage,
                'currency_code' => 'EUR',
                'status' => 'applied',
                'applied_at' => now(),
                'expires_at' => now()->addDays(30), // 30 days to use
                'metadata' => [
                    'discount_id' => $discount->id,
                    'percentage' => $percentage,
                    'first_order_only' => true,
                ],
            ]);

            Log::info('Referred discount created', [
                'reward_id' => $reward->id,
                'referral_id' => $referralId,
                'user_id' => $userId,
                'order_id' => $orderId,
                'discount_id' => $discount->id,
                'percentage' => $percentage,
            ]);

            return $reward;
        } catch (\Exception $e) {
            Log::error('Failed to create referred discount', [
                'referral_id' => $referralId,
                'user_id' => $userId,
                'order_id' => $orderId,
                'percentage' => $percentage,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a bonus reward for the referrer
     */
    public function createReferrerBonus(int $referralId, int $userId, float $amount): ?ReferralReward
    {
        try {
            $reward = ReferralReward::create([
                'referral_id' => $referralId,
                'user_id' => $userId,
                'type' => 'referrer_bonus',
                'amount' => $amount,
                'currency_code' => 'EUR',
                'status' => 'pending',
                'expires_at' => now()->addDays(90), // 90 days to claim
                'metadata' => [
                    'bonus_type' => 'referral_completion',
                    'amount' => $amount,
                ],
            ]);

            Log::info('Referrer bonus created', [
                'reward_id' => $reward->id,
                'referral_id' => $referralId,
                'user_id' => $userId,
                'amount' => $amount,
            ]);

            return $reward;
        } catch (\Exception $e) {
            Log::error('Failed to create referrer bonus', [
                'referral_id' => $referralId,
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a discount in the system for the referred user
     */
    private function createReferralDiscount(int $userId, float $percentage): ?Discount
    {
        try {
            $discount = Discount::create([
                'name' => 'Referral Discount - '.$percentage.'%',
                'code' => 'REFERRAL_'.$userId.'_'.now()->format('Ymd'),
                'type' => 'percentage',
                'value' => $percentage,
                'usage_limit' => 1, // First order only
                'usage_count' => 0,
                'min_spend' => 0,
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
                'status' => 'active',
                'scope' => ['products' => [], 'categories' => [], 'brands' => []],
                'stacking_policy' => 'exclusive',
                'metadata' => [
                    'referral_discount' => true,
                    'user_id' => $userId,
                    'first_order_only' => true,
                ],
                'priority' => 100,
                'exclusive' => true,
                'applies_to_shipping' => false,
                'free_shipping' => false,
                'first_order_only' => true,
                'per_customer_limit' => 1,
            ]);

            return $discount;
        } catch (\Exception $e) {
            Log::error('Failed to create referral discount', [
                'user_id' => $userId,
                'percentage' => $percentage,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Apply a pending reward
     */
    public function applyReward(int $rewardId, ?int $orderId = null): bool
    {
        try {
            $reward = ReferralReward::findOrFail($rewardId);

            if (! $reward->isValid()) {
                throw new \Exception('Reward is not valid or has expired');
            }

            if ($reward->status !== 'pending') {
                throw new \Exception('Reward has already been applied');
            }

            $reward->apply($orderId);

            Log::info('Reward applied', [
                'reward_id' => $rewardId,
                'order_id' => $orderId,
                'type' => $reward->type,
                'amount' => $reward->amount,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to apply reward', [
                'reward_id' => $rewardId,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get pending rewards for a user
     */
    public function getPendingRewards(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return ReferralReward::where('user_id', $userId)
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get applied rewards for a user
     */
    public function getAppliedRewards(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return ReferralReward::where('user_id', $userId)
            ->applied()
            ->orderBy('applied_at', 'desc')
            ->get();
    }

    /**
     * Get total rewards value for a user
     */
    public function getTotalRewardsValue(int $userId): array
    {
        $pending = ReferralReward::where('user_id', $userId)
            ->pending()
            ->sum('amount');

        $applied = ReferralReward::where('user_id', $userId)
            ->applied()
            ->sum('amount');

        $expired = ReferralReward::where('user_id', $userId)
            ->expired()
            ->sum('amount');

        return [
            'pending' => (float) $pending,
            'applied' => (float) $applied,
            'expired' => (float) $expired,
            'total' => (float) ($pending + $applied + $expired),
        ];
    }

    /**
     * Clean up expired rewards
     */
    public function cleanupExpiredRewards(): int
    {
        $expiredRewards = ReferralReward::expired()->get();
        $count = 0;

        foreach ($expiredRewards as $reward) {
            $reward->markAsExpired();
            $count++;
        }

        Log::info('Cleaned up expired rewards', ['count' => $count]);

        return $count;
    }

    /**
     * Validate if user can use referral discount
     */
    public function canUserUseReferralDiscount(int $userId): bool
    {
        // Check if user was referred
        $referral = Referral::where('referred_id', $userId)
            ->where('status', 'completed')
            ->first();

        if (! $referral) {
            return false;
        }

        // Check if user has already used a referral discount
        $usedDiscount = ReferralReward::where('user_id', $userId)
            ->where('type', 'referred_discount')
            ->where('status', 'applied')
            ->exists();

        return ! $usedDiscount;
    }

    /**
     * Get referral discount for user
     */
    public function getReferralDiscountForUser(int $userId): ?ReferralReward
    {
        return ReferralReward::where('user_id', $userId)
            ->where('type', 'referred_discount')
            ->pending()
            ->first();
    }
}
