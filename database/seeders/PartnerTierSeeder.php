<?php

namespace Database\Seeders;

use App\Models\PartnerTier;
use Illuminate\Database\Seeder;

class PartnerTierSeeder extends Seeder
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

        foreach ($tiers as $data) {
            PartnerTier::query()->updateOrCreate(
                ['code' => $data['code']],
                $data,
            );
        }
    }
}

