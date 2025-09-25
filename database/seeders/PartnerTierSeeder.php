<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PartnerTier;
use Illuminate\Database\Seeder;

final class PartnerTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Gold',
                'code' => 'gold',
                'discount_rate' => 0.20,
                'commission_rate' => 0.02,
                'minimum_order_value' => 0.00,
                'is_enabled' => true,
                'benefits' => ['priority_support' => true, 'fast_shipping' => true],
            ],
            [
                'name' => 'Silver',
                'code' => 'silver',
                'discount_rate' => 0.12,
                'commission_rate' => 0.015,
                'minimum_order_value' => 0.00,
                'is_enabled' => true,
                'benefits' => ['priority_support' => false, 'fast_shipping' => true],
            ],
            [
                'name' => 'Bronze',
                'code' => 'bronze',
                'discount_rate' => 0.05,
                'commission_rate' => 0.01,
                'minimum_order_value' => 0.00,
                'is_enabled' => true,
                'benefits' => ['priority_support' => false],
            ],
        ];

        foreach ($tiers as $attributes) {
            if (PartnerTier::query()->where('code', $attributes['code'])->exists()) {
                continue;
            }

            PartnerTier::factory()->state($attributes)->create();
        }
    }
}
