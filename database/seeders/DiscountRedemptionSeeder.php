<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class DiscountRedemptionSeeder extends Seeder
{
    public function run(): void
    {
        // Create some test users
        $users = User::factory()->count(10)->create();

        // Create some discounts
        $discounts = Discount::factory()->count(5)->create();

        // Create discount codes for each discount
        $discountCodes = collect();
        foreach ($discounts as $discount) {
            $codes = DiscountCode::factory()->count(3)->create(['discount_id' => $discount->id]);
            $discountCodes = $discountCodes->merge($codes);
        }

        // Create some orders
        $orders = Order::factory()->count(20)->create();

        // Create redemptions with different statuses
        $statuses = ['pending', 'redeemed', 'expired', 'cancelled'];
        $currencies = ['EUR', 'USD', 'GBP'];

        foreach ($users as $user) {
            // Create 2-5 redemptions per user
            $redemptionCount = fake()->numberBetween(2, 5);

            for ($i = 0; $i < $redemptionCount; $i++) {
                $discount = $discounts->random();
                $code = $discountCodes->where('discount_id', $discount->id)->random();
                $order = $orders->random();
                $status = fake()->randomElement($statuses);
                $currency = fake()->randomElement($currencies);

                DiscountRedemption::factory()->create([
                    'discount_id' => $discount->id,
                    'code_id' => $code->id,
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'amount_saved' => fake()->randomFloat(2, 5, 100),
                    'currency_code' => $currency,
                    'redeemed_at' => fake()->dateTimeBetween('-30 days', 'now'),
                    'status' => $status,
                    'notes' => fake()->optional(0.3)->sentence(),
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'created_by' => $users->random()->id,
                    'updated_by' => $users->random()->id,
                ]);
            }
        }

        // Create some high-value redemptions
        DiscountRedemption::factory()->highValue()->count(5)->create([
            'status' => 'redeemed',
            'currency_code' => 'EUR',
        ]);

        // Create some recent redemptions
        DiscountRedemption::factory()->recent()->count(10)->create([
            'status' => 'redeemed',
        ]);

        // Create some pending redemptions for testing
        DiscountRedemption::factory()->pending()->count(8)->create();

        // Create some expired redemptions
        DiscountRedemption::factory()->expired()->count(6)->create([
            'redeemed_at' => fake()->dateTimeBetween('-60 days', '-30 days'),
        ]);

        // Create some cancelled redemptions
        DiscountRedemption::factory()->cancelled()->count(4)->create();
    }
}
