<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ReferralCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReferralCampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_campaign_can_be_created(): void
    {
        $campaign = ReferralCampaign::factory()->create([
            'name' => 'Test Referral Campaign',
            'description' => 'Test description',
            'is_active' => true,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'reward_amount' => 10.00,
            'reward_type' => 'fixed',
            'max_referrals_per_user' => 5,
            'max_total_referrals' => 100,
        ]);

        $this->assertInstanceOf(ReferralCampaign::class, $campaign);
        $this->assertEquals('Test Referral Campaign', $campaign->name);
        $this->assertEquals('Test description', $campaign->description);
        $this->assertTrue($campaign->is_active);
        $this->assertEquals(10.00, $campaign->reward_amount);
        $this->assertEquals('fixed', $campaign->reward_type);
        $this->assertEquals(5, $campaign->max_referrals_per_user);
        $this->assertEquals(100, $campaign->max_total_referrals);
    }

    public function test_referral_campaign_fillable_attributes(): void
    {
        $campaign = new ReferralCampaign();
        $fillable = $campaign->getFillable();

        $expectedFillable = [
            'name',
            'description',
            'is_active',
            'start_date',
            'end_date',
            'reward_amount',
            'reward_type',
            'max_referrals_per_user',
            'max_total_referrals',
            'conditions',
            'metadata',
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_referral_campaign_casts(): void
    {
        $campaign = ReferralCampaign::factory()->create([
            'is_active' => true,
            'start_date' => '2024-01-01 00:00:00',
            'end_date' => '2024-12-31 23:59:59',
            'reward_amount' => '25.50',
            'max_referrals_per_user' => '10',
            'max_total_referrals' => '500',
            'conditions' => ['min_purchase' => 100],
            'metadata' => ['source' => 'email'],
        ]);

        $this->assertTrue($campaign->is_active);
        $this->assertInstanceOf(\Carbon\Carbon::class, $campaign->start_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $campaign->end_date);
        $this->assertIsFloat($campaign->reward_amount);
        $this->assertIsInt($campaign->max_referrals_per_user);
        $this->assertIsInt($campaign->max_total_referrals);
        $this->assertIsArray($campaign->conditions);
        $this->assertIsArray($campaign->metadata);
    }

    public function test_referral_campaign_has_translations(): void
    {
        $campaign = new ReferralCampaign();
        
        $this->assertTrue(method_exists($campaign, 'getTranslations'));
        $this->assertTrue(method_exists($campaign, 'setTranslation'));
    }

    public function test_referral_campaign_translatable_attributes(): void
    {
        $campaign = new ReferralCampaign();
        
        $this->assertContains('name', $campaign->translatable);
        $this->assertContains('description', $campaign->translatable);
    }

    public function test_referral_campaign_has_referral_codes(): void
    {
        $campaign = ReferralCampaign::factory()->create();
        
        $this->assertTrue(method_exists($campaign, 'referralCodes'));
    }

    public function test_referral_campaign_scopes(): void
    {
        $activeCampaign = ReferralCampaign::factory()->create(['is_active' => true]);
        $inactiveCampaign = ReferralCampaign::factory()->create(['is_active' => false]);

        // Test active scope
        $activeCampaigns = ReferralCampaign::active()->get();
        $this->assertTrue($activeCampaigns->contains($activeCampaign));
        $this->assertFalse($activeCampaigns->contains($inactiveCampaign));
    }

    public function test_referral_campaign_is_active_method(): void
    {
        $activeCampaign = ReferralCampaign::factory()->create(['is_active' => true]);
        $inactiveCampaign = ReferralCampaign::factory()->create(['is_active' => false]);

        $this->assertTrue($activeCampaign->isActive());
        $this->assertFalse($inactiveCampaign->isActive());
    }

    public function test_referral_campaign_is_running_method(): void
    {
        $now = now();
        
        $runningCampaign = ReferralCampaign::factory()->create([
            'is_active' => true,
            'start_date' => $now->copy()->subDays(1),
            'end_date' => $now->copy()->addDays(1),
        ]);
        
        $notStartedCampaign = ReferralCampaign::factory()->create([
            'is_active' => true,
            'start_date' => $now->copy()->addDays(1),
            'end_date' => $now->copy()->addDays(30),
        ]);
        
        $expiredCampaign = ReferralCampaign::factory()->create([
            'is_active' => true,
            'start_date' => $now->copy()->subDays(30),
            'end_date' => $now->copy()->subDays(1),
        ]);

        $this->assertTrue($runningCampaign->isRunning());
        $this->assertFalse($notStartedCampaign->isRunning());
        $this->assertFalse($expiredCampaign->isRunning());
    }

    public function test_referral_campaign_is_expired_method(): void
    {
        $now = now();
        
        $expiredCampaign = ReferralCampaign::factory()->create([
            'end_date' => $now->copy()->subDays(1),
        ]);
        
        $activeCampaign = ReferralCampaign::factory()->create([
            'end_date' => $now->copy()->addDays(1),
        ]);

        $this->assertTrue($expiredCampaign->isExpired());
        $this->assertFalse($activeCampaign->isExpired());
    }

    public function test_referral_campaign_has_started_method(): void
    {
        $now = now();
        
        $startedCampaign = ReferralCampaign::factory()->create([
            'start_date' => $now->copy()->subDays(1),
        ]);
        
        $notStartedCampaign = ReferralCampaign::factory()->create([
            'start_date' => $now->copy()->addDays(1),
        ]);

        $this->assertTrue($startedCampaign->hasStarted());
        $this->assertFalse($notStartedCampaign->hasStarted());
    }

    public function test_referral_campaign_can_accept_referrals_method(): void
    {
        $now = now();
        
        $validCampaign = ReferralCampaign::factory()->create([
            'is_active' => true,
            'start_date' => $now->copy()->subDays(1),
            'end_date' => $now->copy()->addDays(1),
        ]);
        
        $inactiveCampaign = ReferralCampaign::factory()->create([
            'is_active' => false,
            'start_date' => $now->copy()->subDays(1),
            'end_date' => $now->copy()->addDays(1),
        ]);

        $this->assertTrue($validCampaign->canAcceptReferrals());
        $this->assertFalse($inactiveCampaign->canAcceptReferrals());
    }

    public function test_referral_campaign_get_reward_amount_method(): void
    {
        $campaign = ReferralCampaign::factory()->create([
            'reward_amount' => 15.75,
            'reward_type' => 'fixed',
        ]);

        $this->assertEquals(15.75, $campaign->getRewardAmount());
    }

    public function test_referral_campaign_get_remaining_referrals_method(): void
    {
        $campaign = ReferralCampaign::factory()->create([
            'max_total_referrals' => 100,
        ]);

        // Mock the referral codes count
        $this->assertEquals(100, $campaign->getRemainingReferrals());
    }

    public function test_referral_campaign_table_name(): void
    {
        $campaign = new ReferralCampaign();
        $this->assertEquals('referral_campaigns', $campaign->getTable());
    }

    public function test_referral_campaign_uses_activity_log(): void
    {
        $campaign = new ReferralCampaign();
        
        $this->assertTrue(method_exists($campaign, 'getActivitylogOptions'));
    }

    public function test_referral_campaign_factory(): void
    {
        $campaign = ReferralCampaign::factory()->create();

        $this->assertInstanceOf(ReferralCampaign::class, $campaign);
        $this->assertNotEmpty($campaign->name);
        $this->assertNotNull($campaign->reward_amount);
        $this->assertNotNull($campaign->reward_type);
    }
}
