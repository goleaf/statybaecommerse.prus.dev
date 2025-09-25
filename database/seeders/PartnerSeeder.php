<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\PartnerTier;
use Illuminate\Database\Seeder;

final class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        // Create partner tiers using factories
        $tiersData = [
            ['name' => 'Gold', 'code' => 'gold', 'discount_rate' => 0.2, 'commission_rate' => 0.02, 'minimum_order_value' => 0, 'is_enabled' => true, 'benefits' => ['priority_support' => true]],
            ['name' => 'Silver', 'code' => 'silver', 'discount_rate' => 0.12, 'commission_rate' => 0.015, 'minimum_order_value' => 0, 'is_enabled' => true, 'benefits' => ['priority_support' => false]],
            ['name' => 'Bronze', 'code' => 'bronze', 'discount_rate' => 0.05, 'commission_rate' => 0.01, 'minimum_order_value' => 0, 'is_enabled' => true, 'benefits' => ['priority_support' => false]],
        ];

        $tiers = [];
        foreach ($tiersData as $tierData) {
            // Check if tier already exists to maintain idempotency
            $existingTier = PartnerTier::where('code', $tierData['code'])->first();

            if ($existingTier) {
                $existingTier->update($tierData);
                $tiers[$tierData['code']] = $existingTier;
            } else {
                $tier = PartnerTier::factory()
                    ->state($tierData)
                    ->create();
                $tiers[$tierData['code']] = $tier;
            }
        }

        // Create partners using factories with tier relationships
        $partnersData = [
            ['name' => 'Acme', 'code' => 'acme', 'tier_code' => 'gold'],
            ['name' => 'Globex', 'code' => 'globex', 'tier_code' => 'silver'],
            ['name' => 'Initech', 'code' => 'initech', 'tier_code' => 'bronze'],
            ['name' => 'Umbrella', 'code' => 'umbrella', 'tier_code' => 'silver'],
            ['name' => 'Soylent', 'code' => 'soylent', 'tier_code' => 'bronze'],
        ];

        foreach ($partnersData as $partnerData) {
            $tier = $tiers[$partnerData['tier_code']] ?? null;

            // Check if partner already exists to maintain idempotency
            $existingPartner = Partner::where('code', $partnerData['code'])->first();

            if ($existingPartner) {
                $existingPartner->update([
                    'name' => $partnerData['name'],
                    'tier_id' => $tier?->id,
                    'contact_email' => $partnerData['code'].'@example.test',
                    'is_enabled' => true,
                ]);
            } else {
                Partner::factory()
                    ->for($tier, 'tier')
                    ->state([
                        'name' => $partnerData['name'],
                        'code' => $partnerData['code'],
                        'contact_email' => $partnerData['code'].'@example.test',
                        'contact_phone' => '+370600'.str_pad((string) random_int(100, 999), 3, '0', STR_PAD_LEFT),
                        'is_enabled' => true,
                        'discount_rate' => 0,
                        'commission_rate' => 0,
                    ])
                    ->create();
            }
        }

        $this->command->info('Partners and partner tiers seeded successfully.');
    }
}
