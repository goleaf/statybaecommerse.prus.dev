<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

final class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $data = [
            [
                'code' => 'WELCOME10',
                'name' => 'Welcome 10% Off',
                'description' => 'Enjoy 10% off your first order.',
                'type' => 'percentage',
                'value' => 10,
                'minimum_amount' => null,
                'usage_limit' => 100,
                'used_count' => 0,
                'is_active' => true,
                'starts_at' => $now->copy()->subDays(7),
                'expires_at' => $now->copy()->addMonths(3),
            ],
            [
                'code' => 'SAVE20',
                'name' => 'Save 20 EUR',
                'description' => 'Flat 20€ off orders over 100€.',
                'type' => 'fixed',
                'value' => 20,
                'minimum_amount' => 100,
                'usage_limit' => 500,
                'used_count' => 25,
                'is_active' => true,
                'starts_at' => $now->copy()->subMonth(),
                'expires_at' => $now->copy()->addMonth(),
            ],
            [
                'code' => 'NEWUSER5',
                'name' => 'New User €5',
                'description' => '€5 off for new users, no minimum.',
                'type' => 'fixed',
                'value' => 5,
                'minimum_amount' => null,
                'usage_limit' => null,
                'used_count' => 0,
                'is_active' => true,
                'starts_at' => $now->copy()->subDay(),
                'expires_at' => $now->copy()->addDays(30),
            ],
            [
                'code' => 'BF25',
                'name' => 'Black Friday 25%',
                'description' => 'Limited time 25% off during Black Friday.',
                'type' => 'percentage',
                'value' => 25,
                'minimum_amount' => null,
                'usage_limit' => 1000,
                'used_count' => 100,
                'is_active' => true,
                'starts_at' => $now->copy()->setDate($now->year, 11, 25)->startOfDay(),
                'expires_at' => $now->copy()->setDate($now->year, 12, 1)->endOfDay(),
            ],
            [
                'code' => 'EXPIRED50',
                'name' => 'Expired 50% Test',
                'description' => 'Expired coupon for testing filters.',
                'type' => 'percentage',
                'value' => 50,
                'minimum_amount' => null,
                'usage_limit' => 10,
                'used_count' => 3,
                'is_active' => true,
                'starts_at' => $now->copy()->subMonths(2),
                'expires_at' => $now->copy()->subMonth(),
            ],
            [
                'code' => 'INACTIVE10',
                'name' => 'Inactive 10%',
                'description' => 'Disabled coupon to test visibility and actions.',
                'type' => 'percentage',
                'value' => 10,
                'minimum_amount' => null,
                'usage_limit' => null,
                'used_count' => 0,
                'is_active' => false,
                'starts_at' => null,
                'expires_at' => null,
            ],
        ];

        foreach ($data as $row) {
            // Check if coupon already exists to maintain idempotency
            $existingCoupon = Coupon::where('code', $row['code'])->first();
            
            if (!$existingCoupon) {
                Coupon::factory()
                    ->state($row)
                    ->create();
            }
        }
    }
}
