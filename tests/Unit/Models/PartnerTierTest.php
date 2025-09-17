<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Partner;
use App\Models\PartnerTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PartnerTierTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_tier_can_be_created(): void
    {
        $partnerTier = PartnerTier::factory()->create([
            'name' => 'Gold Tier',
            'code' => 'GOLD',
            'discount_rate' => 0.10,
            'commission_rate' => 0.05,
            'minimum_order_value' => 1000.00,
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('partner_tiers', [
            'name' => 'Gold Tier',
            'code' => 'GOLD',
            'discount_rate' => 0.10,
            'commission_rate' => 0.05,
            'minimum_order_value' => 1000.00,
            'is_enabled' => true,
        ]);
    }

    public function test_partner_tier_has_correct_fillable_attributes(): void
    {
        $fillable = [
            'name',
            'code',
            'discount_rate',
            'commission_rate',
            'minimum_order_value',
            'is_enabled',
            'benefits',
        ];

        $this->assertEquals($fillable, (new PartnerTier())->getFillable());
    }

    public function test_partner_tier_has_correct_casts(): void
    {
        $partnerTier = PartnerTier::factory()->create([
            'discount_rate' => '0.10',
            'commission_rate' => '0.05',
            'minimum_order_value' => '1000.00',
            'is_enabled' => '1',
            'benefits' => ['key' => 'value'],
        ]);

        $this->assertIsNumeric($partnerTier->discount_rate);
        $this->assertIsNumeric($partnerTier->commission_rate);
        $this->assertIsNumeric($partnerTier->minimum_order_value);
        $this->assertIsBool($partnerTier->is_enabled);
        $this->assertIsArray($partnerTier->benefits);
    }

    public function test_partner_tier_has_many_partners(): void
    {
        $partnerTier = PartnerTier::factory()->create();
        $partner1 = Partner::factory()->create(['tier_id' => $partnerTier->id]);
        $partner2 = Partner::factory()->create(['tier_id' => $partnerTier->id]);

        $this->assertCount(2, $partnerTier->partners);
        $this->assertTrue($partnerTier->partners->contains($partner1));
        $this->assertTrue($partnerTier->partners->contains($partner2));
    }

    public function test_enabled_scope_returns_only_enabled_tiers(): void
    {
        $enabledTier = PartnerTier::factory()->enabled()->create();
        $disabledTier = PartnerTier::factory()->disabled()->create();

        $enabledTiers = PartnerTier::enabled()->get();

        $this->assertCount(1, $enabledTiers);
        $this->assertTrue($enabledTiers->contains($enabledTier));
        $this->assertFalse($enabledTiers->contains($disabledTier));
    }

    public function test_by_discount_rate_scope_filters_correctly(): void
    {
        $tier1 = PartnerTier::factory()->create(['discount_rate' => 0.10]);
        $tier2 = PartnerTier::factory()->create(['discount_rate' => 0.15]);
        $tier3 = PartnerTier::factory()->create(['discount_rate' => 0.20]);

        $filteredTiers = PartnerTier::byDiscountRate(0.15)->get();

        $this->assertCount(1, $filteredTiers);
        $this->assertTrue($filteredTiers->contains($tier2));
        $this->assertFalse($filteredTiers->contains($tier1));
        $this->assertFalse($filteredTiers->contains($tier3));
    }

    public function test_partner_tier_uses_soft_deletes(): void
    {
        $partnerTier = PartnerTier::factory()->create();
        $partnerTierId = $partnerTier->id;

        $partnerTier->delete();

        $this->assertSoftDeleted('partner_tiers', ['id' => $partnerTierId]);
        $this->assertDatabaseHas('partner_tiers', ['id' => $partnerTierId]);
    }

    public function test_partner_tier_benefits_are_stored_as_json(): void
    {
        $benefits = [
            [
                'key' => 'Priority Support',
                'value' => '24/7 dedicated support'
            ],
            [
                'key' => 'Marketing Materials',
                'value' => 'Access to exclusive resources'
            ]
        ];

        $partnerTier = PartnerTier::factory()->create(['benefits' => $benefits]);

        $this->assertEquals($benefits, $partnerTier->benefits);
        $this->assertIsArray($partnerTier->benefits);
        $this->assertCount(2, $partnerTier->benefits);
    }
}
