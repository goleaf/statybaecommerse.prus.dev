<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CustomerGroup;
use App\Models\Discount;
use App\Models\DiscountCode;
use Illuminate\Database\Seeder;

final class AdminPresetDiscountsSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample discounts using factories

        // 1) VIP 12% sitewide discount
        $vipGroup = CustomerGroup::where('code', 'vip')->first();
        if ($vipGroup) {
            $vipDiscount = Discount::factory()
                ->state([
                    'name' => 'VIP 12% Off',
                    'code' => 'VIP12',
                    'type' => 'percentage',
                    'value' => 12.0,
                    'priority' => 20,
                    'exclusive' => false,
                    'stacking_policy' => 'single_best',
                ])
                ->create();

            // Create condition for customer group if model supports it
            if (method_exists($vipDiscount, 'conditions')) {
                $vipDiscount->conditions()->create([
                    'type' => 'customer_group',
                    'operator' => 'equals_to',
                    'value' => json_encode([$vipGroup->id]),
                    'position' => 0,
                ]);
            }
        }

        // 2) Create additional sample discounts using factory
        Discount::factory()
            ->count(5)
            ->create();

        // 3) Create discount codes using factory
        DiscountCode::factory()
            ->count(10)
            ->create();

        $this->command->info('Admin preset discounts seeded successfully.');
    }
}
