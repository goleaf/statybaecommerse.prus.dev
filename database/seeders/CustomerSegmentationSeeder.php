<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CustomerGroup;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CustomerSegmentationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            // 1) Ensure a few customer groups that act as saved segments
            $segments = [
                [
                    'name' => 'VIP Customers',
                    'slug' => 'vip-customers',
                    'description' => 'High value customers with frequent purchases',
                    'discount_percentage' => 15.00,
                    'is_enabled' => true,
                    'conditions' => [
                        'type' => 'total_spent',
                        'operator' => 'gte',
                        'value' => 1000,
                    ],
                ],
                [
                    'name' => 'Frequent Buyers',
                    'slug' => 'frequent-buyers',
                    'description' => 'Customers with many orders',
                    'discount_percentage' => 5.00,
                    'is_enabled' => true,
                    'conditions' => [
                        'type' => 'order_count',
                        'operator' => 'gt',
                        'value' => 5,
                    ],
                ],
                [
                    'name' => 'Recent Customers',
                    'slug' => 'recent-customers',
                    'description' => 'Customers who ordered in the last 30 days',
                    'discount_percentage' => 0,
                    'is_enabled' => true,
                    'conditions' => [
                        'type' => 'last_order_days',
                        'operator' => 'lte',
                        'value' => 30,
                    ],
                ],
                [
                    'name' => 'Inactive Customers',
                    'slug' => 'inactive-customers',
                    'description' => 'Customers who have been inactive for 6+ months',
                    'discount_percentage' => 0,
                    'is_enabled' => true,
                    'conditions' => [
                        'type' => 'last_order_days',
                        'operator' => 'gt',
                        'value' => 180,
                    ],
                ],
            ];

            $slugToGroup = [];
            foreach ($segments as $data) {
                $group = CustomerGroup::firstOrCreate(
                    ['slug' => $data['slug']],
                    $data,
                );
                $slugToGroup[$data['slug']] = $group;
            }

            // 2) Seed a healthy mix of customers and orders to populate the segmentation page
            // Create customers in different segments
            $vipCustomers = User::factory()->count(5)->create(['is_admin' => false]);
            $frequentCustomers = User::factory()->count(8)->create(['is_admin' => false]);
            $recentCustomers = User::factory()->count(6)->create(['is_admin' => false]);
            $inactiveCustomers = User::factory()->count(6)->create(['is_admin' => false]);

            // Helper to create orders for a given user on a given date with a minimum total
            $createOrder = function (User $user, string $dateString, float $minTotal = 50.0): void {
                /** @var Order $order */
                $order = Order::factory()->create([
                    'user_id' => $user->id,
                    'status' => 'delivered',
                    'currency' => 'EUR',
                    'created_at' => $dateString,
                    'updated_at' => $dateString,
                ]);

                // Ensure the order meets the minimum total while keeping factory-calculated fields coherent
                if ((float) $order->total < $minTotal) {
                    $order->update([
                        'subtotal' => $minTotal,
                        'tax_amount' => round($minTotal * 0.21, 2),
                        'shipping_amount' => 4.99,
                        'discount_amount' => 0,
                        'total' => round($minTotal * 1.21 + 4.99, 2),
                    ]);
                }
            };

            // VIP: many orders and high total across time
            foreach ($vipCustomers as $c) {
                for ($i = 0; $i < 12; $i++) {
                    $date = now()->subDays(random_int(5, 350))->toDateTimeString();
                    $createOrder($c, $date, minTotal: 120.0);
                }
                if (isset($slugToGroup['vip-customers'])) {
                    $c->customerGroups()->syncWithoutDetaching([$slugToGroup['vip-customers']->id]);
                }
            }

            // Frequent: > 5 orders, moderate totals
            foreach ($frequentCustomers as $c) {
                for ($i = 0; $i < 7; $i++) {
                    $date = now()->subDays(random_int(5, 200))->toDateTimeString();
                    $createOrder($c, $date, minTotal: 60.0);
                }
                if (isset($slugToGroup['frequent-buyers'])) {
                    $c->customerGroups()->syncWithoutDetaching([$slugToGroup['frequent-buyers']->id]);
                }
            }

            // Recent: 1-3 orders in last 30 days
            foreach ($recentCustomers as $c) {
                $count = random_int(1, 3);
                for ($i = 0; $i < $count; $i++) {
                    $date = now()->subDays(random_int(0, 30))->toDateTimeString();
                    $createOrder($c, $date, minTotal: 50.0);
                }
                if (isset($slugToGroup['recent-customers'])) {
                    $c->customerGroups()->syncWithoutDetaching([$slugToGroup['recent-customers']->id]);
                }
            }

            // Inactive: some orders older than 6 months
            foreach ($inactiveCustomers as $c) {
                $count = random_int(0, 2);
                for ($i = 0; $i < $count; $i++) {
                    $date = now()->subDays(random_int(200, 720))->toDateTimeString();
                    $createOrder($c, $date, minTotal: 40.0);
                }
                if (isset($slugToGroup['inactive-customers'])) {
                    $c->customerGroups()->syncWithoutDetaching([$slugToGroup['inactive-customers']->id]);
                }
            }
        });
    }
}

