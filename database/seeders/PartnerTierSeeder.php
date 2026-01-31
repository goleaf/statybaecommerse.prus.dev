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
                'name'            => 'Bronze',
                'code'            => 'bronze',
                'priority'        => 1,
                'discount_rate'   => 0.05,
                'commission_rate' => 0.01,
                'is_enabled'      => true,
            ],
            [
                'name'            => 'Silver',
                'code'            => 'silver',
                'priority'        => 2,
                'discount_rate'   => 0.12,
                'commission_rate' => 0.015,
                'is_enabled'      => true,
            ],
            [
                'name'            => 'Gold',
                'code'            => 'gold',
                'priority'        => 3,
                'discount_rate'   => 0.20,
                'commission_rate' => 0.02,
                'is_enabled'      => true,
            ],
            [
                'name'            => 'Platinum',
                'code'            => 'platinum',
                'priority'        => 4,
                'discount_rate'   => 0.25,
                'commission_rate' => 0.03,
                'is_enabled'      => true,
            ],
        ];

        foreach ($tiers as $tier) {
            PartnerTier::updateOrCreate(['code' => $tier['code']], $tier);
        }
    }
}
