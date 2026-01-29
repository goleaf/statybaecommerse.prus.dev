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
            /** @var Discount $vipDiscount */
            $vipDiscount = Discount::factory()
                ->state([
                    'name'            => 'VIP 12% Off',
                    'type'            => 'percentage',
                    'value'           => 12.0,
                    'priority'        => 20,
                    'exclusive'       => false,
                    'stacking_policy' => 'single_best',
                ])
                ->create();

            // Create the code separately
            $codeData = DiscountCode::factory()->make([
                'discount_id' => $vipDiscount->id,
                'code'        => 'VIP12',
            ])->toArray();

            DiscountCode::updateOrCreate(
                ['code' => 'VIP12'],
                $codeData
            );

            // Create condition for customer group if model supports it
            if (method_exists($vipDiscount, 'conditions')) {
                $vipDiscount->conditions()->create([
                    'type'     => 'customer_group',
                    'operator' => 'equals_to',
                    'value'    => json_encode([$vipGroup->id]),
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
