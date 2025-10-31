<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class CustomerSegmentationSeeder extends Seeder
{
    public function run(): void
    {
        $customerGroups = [
            [
                'name'                 => 'VIP Customers',
                'slug'                 => 'vip-customers',
                'description'          => 'High-value customers with special privileges and exclusive access to premium products',
                'discount_percentage'  => 15.0,
                'minimum_order_amount' => 1000.0,
                'credit_limit'         => 5000.0,
                'payment_terms'        => 'net_60',
                'is_enabled'           => true,
                'is_active'            => true,
                'has_special_pricing'  => true,
                'has_volume_discounts' => true,
                'can_view_prices'      => true,
                'can_place_orders'     => true,
                'can_view_catalog'     => true,
                'can_use_coupons'      => true,
                'sort_order'           => 10,
                'type'                 => 'vip',
                'metadata'             => [
                    'type'                 => 'vip',
                    'has_special_pricing'  => true,
                    'has_volume_discounts' => true,
                    'can_view_prices'      => true,
                    'can_place_orders'     => true,
                    'can_view_catalog'     => true,
                    'can_use_coupons'      => true,
                    'sort_order'           => 10,
                ],
                'conditions'           => [
                    'min_order_value' => 1000,
                    'min_orders'      => 10,
                    'loyalty_months'  => 12,
                ],
            ],
            [
                'name'                 => 'Regular Customers',
                'slug'                 => 'regular-customers',
                'description'          => 'Standard customers with basic benefits and standard pricing',
                'discount_percentage'  => 5.0,
                'minimum_order_amount' => 100.0,
                'credit_limit'         => 1000.0,
                'payment_terms'        => 'net_30',
                'is_enabled'           => true,
                'is_active'            => true,
                'has_special_pricing'  => false,
                'has_volume_discounts' => false,
                'can_view_prices'      => true,
                'can_place_orders'     => true,
                'can_view_catalog'     => true,
                'can_use_coupons'      => true,
                'sort_order'           => 20,
                'type'                 => 'retail',
                'metadata'             => [
                    'type'                 => 'retail',
                    'has_special_pricing'  => false,
                    'has_volume_discounts' => false,
                    'can_view_prices'      => true,
                    'can_place_orders'     => true,
                    'can_view_catalog'     => true,
                    'can_use_coupons'      => true,
                    'sort_order'           => 20,
                ],
                'conditions'           => [
                    'min_order_value' => 100,
                    'min_orders'      => 3,
                ],
            ],
            [
                'name'                 => 'New Customers',
                'slug'                 => 'new-customers',
                'description'          => 'First-time customers with welcome offers and special introductory pricing',
                'discount_percentage'  => 10.0,
                'minimum_order_amount' => 0.0,
                'credit_limit'         => 500.0,
                'payment_terms'        => 'net_30',
                'is_enabled'           => true,
                'is_active'            => true,
                'has_special_pricing'  => true,
                'has_volume_discounts' => false,
                'can_view_prices'      => true,
                'can_place_orders'     => true,
                'can_view_catalog'     => true,
                'can_use_coupons'      => true,
                'sort_order'           => 30,
                'type'                 => 'new',
                'metadata'             => [
                    'type'                 => 'new',
                    'has_special_pricing'  => true,
                    'has_volume_discounts' => false,
                    'can_view_prices'      => true,
                    'can_place_orders'     => true,
                    'can_view_catalog'     => true,
                    'can_use_coupons'      => true,
                    'sort_order'           => 30,
                ],
                'conditions'           => [
                    'max_orders'        => 1,
                    'registration_days' => 30,
                ],
            ],
            [
                'name'                 => 'Bulk Buyers',
                'slug'                 => 'bulk-buyers',
                'description'          => 'Customers who purchase large quantities with volume discounts',
                'discount_percentage'  => 12.0,
                'minimum_order_amount' => 500.0,
                'credit_limit'         => 2500.0,
                'payment_terms'        => 'net_45',
                'is_enabled'           => true,
                'is_active'            => true,
                'has_special_pricing'  => true,
                'has_volume_discounts' => true,
                'can_view_prices'      => true,
                'can_place_orders'     => true,
                'can_view_catalog'     => true,
                'can_use_coupons'      => true,
                'sort_order'           => 40,
                'type'                 => 'wholesale',
                'metadata'             => [
                    'type'                 => 'wholesale',
                    'has_special_pricing'  => true,
                    'has_volume_discounts' => true,
                    'can_view_prices'      => true,
                    'can_place_orders'     => true,
                    'can_view_catalog'     => true,
                    'can_use_coupons'      => true,
                    'sort_order'           => 40,
                ],
                'conditions'           => [
                    'min_quantity'    => 50,
                    'min_order_value' => 500,
                ],
            ],
            [
                'name'                 => 'Corporate Clients',
                'slug'                 => 'corporate-clients',
                'description'          => 'Business customers with negotiated pricing and special terms',
                'discount_percentage'  => 20.0,
                'minimum_order_amount' => 2000.0,
                'credit_limit'         => 15000.0,
                'payment_terms'        => 'net_30',
                'is_enabled'           => true,
                'is_active'            => true,
                'has_special_pricing'  => true,
                'has_volume_discounts' => true,
                'can_view_prices'      => true,
                'can_place_orders'     => true,
                'can_view_catalog'     => true,
                'can_use_coupons'      => true,
                'sort_order'           => 50,
                'type'                 => 'corporate',
                'metadata'             => [
                    'type'                 => 'corporate',
                    'has_special_pricing'  => true,
                    'has_volume_discounts' => true,
                    'can_view_prices'      => true,
                    'can_place_orders'     => true,
                    'can_view_catalog'     => true,
                    'can_use_coupons'      => true,
                    'sort_order'           => 50,
                ],
                'conditions'           => [
                    'customer_type'      => 'business',
                    'min_monthly_volume' => 5000,
                ],
            ],
            [
                'name'                 => 'Inactive Customers',
                'slug'                 => 'inactive-customers',
                'description'          => "Customers who haven't made a purchase in the last 6 months",
                'discount_percentage'  => 0.0,
                'minimum_order_amount' => 0.0,
                'credit_limit'         => 0.0,
                'payment_terms'        => 'net_30',
                'is_enabled'           => false,
                'is_active'            => false,
                'has_special_pricing'  => false,
                'has_volume_discounts' => false,
                'can_view_prices'      => false,
                'can_place_orders'     => false,
                'can_view_catalog'     => true,
                'can_use_coupons'      => true,
                'sort_order'           => 60,
                'type'                 => 'dormant',
                'metadata'             => [
                    'type'                 => 'dormant',
                    'has_special_pricing'  => false,
                    'has_volume_discounts' => false,
                    'can_view_prices'      => false,
                    'can_place_orders'     => false,
                    'can_view_catalog'     => true,
                    'can_use_coupons'      => true,
                    'sort_order'           => 60,
                ],
                'conditions'           => [
                    'last_order_days'       => 180,
                    'reactivation_campaign' => true,
                ],
            ],
        ];

        foreach ($customerGroups as $groupData) {
            $groupData['code'] = $groupData['code'] ?? Str::upper(Str::of($groupData['slug'])->replace('-', '_'));

            // Persist deterministic segmentation data while keeping the seeder idempotent so
            // repeated executions during local development or CI do not trigger unique slug
            // violations on the `customer_groups` table.
            CustomerGroup::query()->updateOrCreate(
                ['slug' => $groupData['slug']],
                $groupData,
            );
        }

        // Assign some users to customer groups using relationships
        $users = User::limit(10)->get();
        $groups = CustomerGroup::enabled()->get();

        $users->each(function (User $user, int $index) use ($groups) {
            if ($groups->isNotEmpty()) {
                $group = $groups[$index % $groups->count()];
                $user->customerGroups()->syncWithoutDetaching([$group->id]);
            }
        });
    }
}
