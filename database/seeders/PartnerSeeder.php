<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Partner;

final class PartnerSeeder extends \Database\Seeders\BaseSeeder
{
    public function run(): void
    {
        // Create partners using factories with direct discount and commission rates
        $partnersData = [
            [
                'name'            => 'Acme',
                'code'            => 'acme',
                'discount_rate'   => 0.20,
                'commission_rate' => 0.02,
            ],
            [
                'name'            => 'Globex',
                'code'            => 'globex',
                'discount_rate'   => 0.12,
                'commission_rate' => 0.015,
            ],
            [
                'name'            => 'Initech',
                'code'            => 'initech',
                'discount_rate'   => 0.05,
                'commission_rate' => 0.01,
            ],
            [
                'name'            => 'Umbrella',
                'code'            => 'umbrella',
                'discount_rate'   => 0.12,
                'commission_rate' => 0.015,
            ],
            [
                'name'            => 'Soylent',
                'code'            => 'soylent',
                'discount_rate'   => 0.05,
                'commission_rate' => 0.01,
            ],
            [
                'name'            => 'Wayne Enterprises',
                'code'            => 'wayne-enterprises',
                'discount_rate'   => 0.25,
                'commission_rate' => 0.03,
            ],
        ];

        foreach ($partnersData as $partnerData) {
            // Check if partner already exists to maintain idempotency
            $existingPartner = Partner::where('code', $partnerData['code'])->first();

            if ($existingPartner) {
                $existingPartner->update([
                    'name'            => $partnerData['name'],
                    'contact_email'   => $partnerData['code'] . '@example.test',
                    'discount_rate'   => $partnerData['discount_rate'],
                    'commission_rate' => $partnerData['commission_rate'],
                    'is_enabled'      => true,
                ]);
            } else {
                Partner::factory()
                    ->state([
                        'name'            => $partnerData['name'],
                        'code'            => $partnerData['code'],
                        'contact_email'   => $partnerData['code'] . '@example.test',
                        'contact_phone'   => '+370600' . str_pad((string) random_int(100, 999), 3, '0', STR_PAD_LEFT),
                        'is_enabled'      => true,
                        'discount_rate'   => $partnerData['discount_rate'],
                        'commission_rate' => $partnerData['commission_rate'],
                    ])
                    ->create();
            }
        }

        $this->command->info('Partners seeded successfully.');
    }
}
