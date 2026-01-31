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

final class UserComprehensiveRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensurePrerequisites();

        $this->command->info('Loading prerequisites...');
        $partnerIds = Partner::pluck('id');
        $couponIds = Coupon::pluck('id');
        $discountIds = Discount::pluck('id');
        $discountCodeIds = DiscountCode::pluck('id');
        $referralIds = Referral::pluck('id');
        $orderIds = Order::limit(100)->pluck('id'); // Pick a subset of orders to link to

        $this->command->info('Starting comprehensive user relationship seeding...');

        User::chunk(50, function ($users) use ($partnerIds, $couponIds, $discountIds, $discountCodeIds, $referralIds, $orderIds) {
            foreach ($users as $user) {
                // 1. Partner (Exact 1)
                if ($partnerIds->isNotEmpty()) {
                    $randomPartnerId = $partnerIds->random();
                    $user->partners()->sync([$randomPartnerId]);
                }

                // 2. Coupon Usage (Exact 1)
                $this->enforceOneCouponUsage($user, $couponIds, $orderIds);

                // 3. Discount Redemption (Exact 1)
                $this->enforceOneDiscountRedemption($user, $discountIds, $discountCodeIds, $orderIds);

                // 4. Referral Reward (Exact 1)
                $this->enforceOneReferralReward($user, $referralIds);

                // 5. Subscriber (Exact 1)
                $this->enforceSubscriber($user);
            }
        });

        $this->command->info('User relationships comprehensive seeding completed.');
    }

    private function ensurePrerequisites(): void
    {
        if (Partner::count() === 0) {
            Partner::factory(3)->create();
        }
        if (Coupon::count() === 0) {
            Coupon::factory(3)->create();
        }
        if (Discount::count() === 0) {
            Discount::factory(3)->create();
        }
        if (DiscountCode::count() === 0) {
            DiscountCode::factory(3)->create();
        }
        if (Referral::count() === 0) {
            Referral::factory(3)->create();
        }
        if (Order::count() === 0) {
            Order::factory(5)->create();
        }
    }

    private function enforceOneCouponUsage(User $user, $couponIds, $orderIds): void
    {
        $relation = $user->couponUsages()->withoutGlobalScopes();
        if ($relation->count() === 1) {
            return;
        }

        if ($relation->count() > 1) {
            $keep = $relation->first();
            $relation->where('id', '!=', $keep->id)->delete();

            return;
        }

        if ($couponIds->isNotEmpty() && $orderIds->isNotEmpty()) {
            CouponUsage::create([
                'coupon_id'       => $couponIds->random(),
                'user_id'         => $user->id,
                'order_id'        => $orderIds->random(),
                'discount_amount' => 10.00,
                'used_at'         => now(),
            ]);
        }
    }

    private function enforceOneDiscountRedemption(User $user, $discountIds, $discountCodeIds, $orderIds): void
    {
        $relation = $user->discountRedemptions()->withoutGlobalScopes();
        if ($relation->count() === 1) {
            return;
        }

        if ($relation->count() > 1) {
            $keep = $relation->first();
            $relation->where('id', '!=', $keep->id)->delete();

            return;
        }

        if ($discountIds->isNotEmpty() && $orderIds->isNotEmpty()) {
            DiscountRedemption::create([
                'discount_id'  => $discountIds->random(),
                'code_id'      => $discountCodeIds->isNotEmpty() ? $discountCodeIds->random() : null,
                'user_id'      => $user->id,
                'order_id'     => $orderIds->random(),
                'amount_saved' => 5.00,
                'redeemed_at'  => now(),
                'status'       => 'redeemed',
            ]);
        }
    }

    private function enforceOneReferralReward(User $user, $referralIds): void
    {
        $relation = $user->referralRewards()->withoutGlobalScopes();
        if ($relation->count() === 1) {
            return;
        }

        if ($relation->count() > 1) {
            $keep = $relation->first();
            $relation->where('id', '!=', $keep->id)->delete();

            return;
        }

        if ($referralIds->isNotEmpty()) {
            ReferralReward::create([
                'referral_id'   => $referralIds->random(),
                'user_id'       => $user->id,
                'amount'        => 20.00,
                'currency_code' => 'EUR',
                'status'        => 'pending',
                'title'         => ['en' => 'Referral Bonus', 'lt' => 'Premija'],
                'type'          => 'credit',
            ]);
        }
    }

    private function enforceSubscriber(User $user): void
    {
        if ($user->subscriber()->withoutGlobalScopes()->exists()) {
            return;
        }

        Subscriber::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'status'     => 'active',
            'first_name' => $user->first_name ?? 'User',
            'last_name'  => $user->last_name ?? '',
        ]);
    }
}
