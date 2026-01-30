<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class UserComprehensiveRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensurePrerequisites();

        $partnerIds = Partner::pluck('id');
        // We'll use random existing orders/coupons/etc for the logic
        // But for specific relations we might need to create new checking models or pick randoms
        
        $this->command->info('Starting comprehensive user relationship seeding...');

        User::chunk(100, function ($users) use ($partnerIds) {
            foreach ($users as $user) {
                // 1. Partner (Exact 1)
                if ($partnerIds->isNotEmpty()) {
                    $randomPartnerId = $partnerIds->random();
                    $user->partners()->sync([$randomPartnerId]);
                }

                // 2. Coupon Usage (Exact 1)
                $this->enforceOneCouponUsage($user);

                // 3. Discount Redemption (Exact 1)
                $this->enforceOneDiscountRedemption($user);

                // 4. Referral Reward (Exact 1)
                $this->enforceOneReferralReward($user);

                // 5. Subscriber (Exact 1)
                $this->enforceSubscriber($user);
            }
        });

        $this->command->info('User relationships comprehensive seeding completed.');
    }

    private function ensurePrerequisites(): void
    {
        // Ensure at least one Partner exists
        if (Partner::count() === 0) {
            Partner::factory(3)->create();
        }

        // Ensure at least one Coupon exists
        if (Coupon::count() === 0) {
            Coupon::factory(3)->create();
        }

        // Ensure at least one Discount exists
        if (Discount::count() === 0) {
            Discount::factory(3)->create();
        }
        
        // Ensure at least one DiscountCode exists
        if (DiscountCode::count() === 0) {
            // Usually factories handle creating parent discount if needed
             DiscountCode::factory(3)->create();
        }

        // Ensure at least one Referral exists (needed for Reward)
        if (Referral::count() === 0) {
            Referral::factory(3)->create();
        }
        
        // Ensure Orders exist to link usages to
        if (Order::count() === 0) {
            Order::factory(5)->create();
        }
    }

    private function enforceOneCouponUsage(User $user): void
    {
        // Use withoutGlobalScopes to bypass UserOwnedScope
        $relation = $user->couponUsages()->withoutGlobalScopes();
        $count = $relation->count();

        if ($count === 1) {
            return;
        }

        if ($count > 1) {
            // Keep one, delete others
            $keep = $relation->first();
            $relation->where('id', '!=', $keep->id)->delete();
            return;
        }

        $coupon = Coupon::inRandomOrder()->first();
        $order = Order::inRandomOrder()->first();

        if ($coupon && $order) {
           CouponUsage::factory()->create([
               'coupon_id' => $coupon->id,
               'user_id' => $user->id,
               'order_id' => $order->id,
           ]); 
        }
    }

    private function enforceOneDiscountRedemption(User $user): void
    {
        $relation = $user->discountRedemptions()->withoutGlobalScopes();
        $count = $relation->count();

        if ($count === 1) {
            return;
        }

        if ($count > 1) {
             $keep = $relation->first();
             $relation->where('id', '!=', $keep->id)->delete();
             return;
        }

        $discount = Discount::inRandomOrder()->first();
        $order = Order::inRandomOrder()->first();
        
        if ($discount && $order) {
            DiscountRedemption::factory()->create([
                'discount_id' => $discount->id,
                'user_id' => $user->id,
                'order_id' => $order->id,
            ]);
        }
    }

    private function enforceOneReferralReward(User $user): void
    {
        $relation = $user->referralRewards()->withoutGlobalScopes();
        $count = $relation->count();

        if ($count === 1) {
            return;
        }

        if ($count > 1) {
            $keep = $relation->first();
            $relation->where('id', '!=', $keep->id)->delete();
            return;
        }

        $referral = Referral::inRandomOrder()->first();
        if ($referral) {
            ReferralReward::factory()->create([
                'referral_id' => $referral->id,
                'user_id' => $user->id,
            ]);
        }
    }

    private function enforceSubscriber(User $user): void
    {
        // Subscriber typically doesn't have UserOwnedScope but good to be safe if implemented later
        if ($user->subscriber()->withoutGlobalScopes()->exists()) {
            return;
        }

        Subscriber::factory()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => 'active',
        ]);
    }
}
