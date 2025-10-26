<?php

declare(strict_types=1);

namespace Tests\Feature\Seeders;

use App\Models\Partner;
use App\Models\PartnerTier;
use Database\Seeders\PartnerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PartnerSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_seeder_provisions_all_required_tiers(): void
    {
        // Execute the seeder so we can validate the provisioned partner tiers and their commercial settings.
        $this->seed(PartnerSeeder::class);

        // Confirm each expected tier code exists and carries the configured discount and commission rates.
        $expectedTiers = [
            'bronze'   => ['discount_rate' => 0.05, 'commission_rate' => 0.01],
            'silver'   => ['discount_rate' => 0.12, 'commission_rate' => 0.015],
            'gold'     => ['discount_rate' => 0.20, 'commission_rate' => 0.02],
            'platinum' => ['discount_rate' => 0.25, 'commission_rate' => 0.03],
        ];

        foreach ($expectedTiers as $code => $rates) {
            // Retrieve the tier by its business code to make the assertion human readable when it fails.
            $tier = PartnerTier::query()->where('code', $code)->first();
            self::assertNotNull($tier, sprintf('Failed asserting that tier %s exists.', $code));
            self::assertSame($rates['discount_rate'], (float) $tier->getAttribute('discount_rate'));
            self::assertSame($rates['commission_rate'], (float) $tier->getAttribute('commission_rate'));
        }
    }

    public function test_partner_seeder_assigns_partners_to_each_tier(): void
    {
        // Run the seeder so the partner catalogue mirrors production defaults.
        $this->seed(PartnerSeeder::class);

        // Verify that at least one partner exists for every tier so analytics have attribution data.
        $tiersWithPartners = Partner::query()
            ->withoutGlobalScopes()
            ->selectRaw('partner_tiers.code as tier_code, COUNT(partners.id) as partner_count')
            ->join('partner_tiers', 'partner_tiers.id', '=', 'partners.tier_id')
            ->where('partners.is_enabled', true)
            ->where('partner_tiers.is_enabled', true)
            ->groupBy('partner_tiers.code')
            ->pluck('partner_count', 'tier_code');

        foreach (['bronze', 'silver', 'gold', 'platinum'] as $code) {
            self::assertTrue($tiersWithPartners->has($code), sprintf('Failed asserting that tier %s has partners.', $code));
            self::assertGreaterThan(0, (int) $tiersWithPartners->get($code));
        }
    }
}
