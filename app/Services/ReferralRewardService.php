<?php

declare (strict_types=1);
namespace App\Services;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralReward;
use Illuminate\Support\Facades\Log;
/**
 * ReferralRewardService
 * 
 * Service class containing ReferralRewardService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class ReferralRewardService
{
    /**
     * Handle createReferredDiscount functionality with proper error handling.
     * @param int $referralId
     * @param int $userId
     * @param int $orderId
     * @param float $percentage
     * @return ReferralReward|null
     */
    public function createReferredDiscount(int $referralId, int $userId, int $orderId, float $percentage): ?ReferralReward
    {
        try {
            // Create the discount in the system
            $discount = $this->createReferralDiscount($userId, $percentage);
            if (!$discount) {
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
                'expires_at' => now()->addDays(30),
                // 30 days to use
                'metadata' => ['discount_id' => $discount->id, 'percentage' => $percentage, 'first_order_only' => true],
            ]);
            Log::info('Referred discount created', ['reward_id' => $reward->id, 'referral_id' => $referralId, 'user_id' => $userId, 'order_id' => $orderId, 'discount_id' => $discount->id, 'percentage' => $percentage]);
            return $reward;
        } catch (\Exception $e) {
            Log::error('Failed to create referred discount', ['referral_id' => $referralId, 'user_id' => $userId, 'order_id' => $orderId, 'percentage' => $percentage, 'error' => $e->getMessage()]);
            return null;
        }
    }
    /**
     * Handle createReferrerBonus functionality with proper error handling.
     * @param int $referralId
     * @param int $userId
     * @param float $amount
     * @return ReferralReward|null
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
                'expires_at' => now()->addDays(90),
                // 90 days to claim
                'metadata' => ['bonus_type' => 'referral_completion', 'amount' => $amount],
            ]);
            Log::info('Referrer bonus created', ['reward_id' => $reward->id, 'referral_id' => $referralId, 'user_id' => $userId, 'amount' => $amount]);
            return $reward;
        } catch (\Exception $e) {
            Log::error('Failed to create referrer bonus', ['referral_id' => $referralId, 'user_id' => $userId, 'amount' => $amount, 'error' => $e->getMessage()]);
            return null;
        }
    }
    /**
     * Handle createReferralDiscount functionality with proper error handling.
     * @param int $userId
     * @param float $percentage
     * @return Discount|null
     */
    private function createReferralDiscount(int $userId, float $percentage): ?Discount
    {
        try {
            $discount = Discount::create([
                'name' => 'Referral Discount - ' . $percentage . '%',
                'slug' => 'referral-' . $userId . '-' . now()->format('Ymd'),
                'type' => 'percentage',
                'value' => $percentage,
                'usage_limit' => 1,
                // First order only
                'usage_count' => 0,
                'minimum_amount' => 0,
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
                'status' => 'active',
                'scope' => ['products' => [], 'categories' => [], 'brands' => []],
                'stacking_policy' => 'exclusive',
                'metadata' => ['referral_discount' => true, 'user_id' => $userId, 'first_order_only' => true],
                'priority' => 100,
                'exclusive' => true,
                'applies_to_shipping' => false,
                'free_shipping' => false,
                'first_order_only' => true,
                'per_customer_limit' => 1,
            ]);
            return $discount;
        } catch (\Exception $e) {
            Log::error('Failed to create referral discount', ['user_id' => $userId, 'percentage' => $percentage, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return null;
        }
    }
    /**
     * Handle applyReward functionality with proper error handling.
     * @param int $rewardId
     * @param int|null $orderId
     * @return bool
     */
    public function applyReward(int $rewardId, ?int $orderId = null): bool
    {
        try {
            $reward = ReferralReward::findOrFail($rewardId);
            if (!$reward->isValid()) {
                throw new \Exception('Reward is not valid or has expired');
            }
            if ($reward->status !== 'pending') {
                throw new \Exception('Reward has already been applied');
            }
            $reward->apply($orderId);
            Log::info('Reward applied', ['reward_id' => $rewardId, 'order_id' => $orderId, 'type' => $reward->type, 'amount' => $reward->amount]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to apply reward', ['reward_id' => $rewardId, 'order_id' => $orderId, 'error' => $e->getMessage()]);
            return false;
        }
    }
    /**
     * Handle getPendingRewards functionality with proper error handling.
     * @param int $userId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getPendingRewards(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return ReferralReward::where('user_id', $userId)->pending()->orderBy('created_at', 'desc')->get();
    }
    /**
     * Handle getAppliedRewards functionality with proper error handling.
     * @param int $userId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getAppliedRewards(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return ReferralReward::where('user_id', $userId)->applied()->orderBy('applied_at', 'desc')->get();
    }
    /**
     * Handle getTotalRewardsValue functionality with proper error handling.
     * @param int $userId
     * @return array
     */
    public function getTotalRewardsValue(int $userId): array
    {
        $pending = ReferralReward::where('user_id', $userId)->pending()->sum('amount');
        $applied = ReferralReward::where('user_id', $userId)->applied()->sum('amount');
        $expired = ReferralReward::where('user_id', $userId)->expired()->sum('amount');
        return ['pending' => (float) $pending, 'applied' => (float) $applied, 'expired' => (float) $expired, 'total' => (float) ($pending + $applied + $expired)];
    }
    /**
     * Handle cleanupExpiredRewards functionality with proper error handling.
     * @return int
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
     * Handle canUserUseReferralDiscount functionality with proper error handling.
     * @param int $userId
     * @return bool
     */
    public function canUserUseReferralDiscount(int $userId): bool
    {
        // Check if user was referred
        $referral = Referral::where('referred_id', $userId)->where('status', 'completed')->first();
        if (!$referral) {
            return false;
        }
        // Check if user has a referral discount that can be used
        $availableDiscount = ReferralReward::where('user_id', $userId)->where('type', 'referred_discount')->where('status', 'applied')->exists();
        return $availableDiscount;
    }
    /**
     * Handle getReferralDiscountForUser functionality with proper error handling.
     * @param int $userId
     * @return ReferralReward|null
     */
    public function getReferralDiscountForUser(int $userId): ?ReferralReward
    {
        return ReferralReward::where('user_id', $userId)->where('type', 'referred_discount')->pending()->first();
    }
}