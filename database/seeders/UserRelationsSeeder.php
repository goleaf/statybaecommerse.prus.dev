<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use App\Models\CouponUsage;
use App\Models\CustomerGroup;
use App\Models\DiscountRedemption;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\ReferralReward;
use App\Models\Subscriber;
use App\Models\User;

final class UserRelationsSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Cleanup existing test user to allow re-runs
        User::query()->where('email', 'info@egisstatyba.lt')->each(function (User $user): void {
            $user->customerGroups()->detach();
            $user->partners()->detach();
            $user->delete();
        });
        Subscriber::query()->where('email', 'info@egisstatyba.lt')->delete();

        // 1. Create a primary test user with ALL relationships
        $testUser = User::factory()->create([
            'name'     => 'Test User All Relations',
            'email'    => 'info@egisstatyba.lt',
            'is_admin' => true,
        ]);

        $this->seedUserRelations($testUser);

        // 2. Create several more users with varied relations to populate the list
        User::factory()->count(5)->create()->each(function (User $user): void {
            $this->seedUserRelations($user, rand(1, 3));
        });
    }

    /**
     * Seed relations for a specific user.
     */
    private function seedUserRelations(User $user, int $count = 5): void
    {
        // 1. Orders with items and shipping
        Order::factory()->count($count)->create(['user_id' => $user->id])->each(function (Order $order): void {
            \App\Models\OrderItem::factory()->count(rand(1, 4))->create(['order_id' => $order->id]);
            \App\Models\OrderShipping::factory()->create(['order_id' => $order->id]);
        });

        // 2. Addresses
        Address::factory()->count(rand(1, 3))->create(['user_id' => $user->id]);

        // 3. Customer Groups
        $groups = CustomerGroup::all();
        if ($groups->isEmpty()) {
            $groups = CustomerGroup::factory()->count(3)->create();
        }
        $user->customerGroups()->sync($groups->random(min(2, $groups->count()))->pluck('id'));

        // 4. Partners
        $partners = Partner::all();
        if ($partners->isEmpty()) {
            $partners = Partner::factory()->count(3)->create();
        }
        $user->partners()->sync($partners->random(min(1, $partners->count()))->pluck('id'));

        // 5. Referral Codes
        ReferralCode::factory()->count(rand(1, 2))->create(['user_id' => $user->id]);

        // 6. Referrals (Users referred BY this user)
        Referral::factory()->count($count)->create([
            'referrer_id' => $user->id,
        ]);

        // 7. Coupon Usages
        CouponUsage::factory()->count($count)->create(['user_id' => $user->id]);

        // 8. Discount Redemptions
        DiscountRedemption::factory()->count($count)->create(['user_id' => $user->id]);

        // 9. Referral Rewards
        ReferralReward::factory()->count($count)->create(['user_id' => $user->id]);

        // 10. Notifications
        Notification::factory()->count($count)->create([
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'user_id'         => $user->id,
        ]);

        // 11. Subscriber
        $subscriber = Subscriber::query()->where('email', $user->email)->first();
        if ($subscriber) {
            $subscriber->update([
                'user_id'       => $user->id,
                'first_name'    => $user->first_name ?? 'First',
                'last_name'     => $user->last_name ?? 'Last',
                'status'        => 'active',
                'subscribed_at' => now(),
            ]);
        } else {
            Subscriber::create([
                'email'         => $user->email,
                'user_id'       => $user->id,
                'first_name'    => $user->first_name ?? 'First',
                'last_name'     => $user->last_name ?? 'Last',
                'status'        => 'active',
                'subscribed_at' => now(),
            ]);
        }
    }
}
