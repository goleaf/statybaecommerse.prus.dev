<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\ReferralCode;
use App\Models\User;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\ReferralCampaign;
use App\Models\ReferralCodeUsageLog;
use App\Models\ReferralCodeStatistics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReferralCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_referral_code(): void
    {
        $user = User::factory()->create();
        
        $referralCode = ReferralCode::create([
            'user_id' => $user->id,
            'code' => 'TEST123',
            'is_active' => true,
            'title' => ['lt' => 'Test kodas', 'en' => 'Test code'],
            'description' => ['lt' => 'Test aprašymas', 'en' => 'Test description'],
            'usage_limit' => 100,
            'usage_count' => 0,
            'reward_amount' => 10.50,
            'reward_type' => 'fixed',
            'source' => 'admin',
            'tags' => ['test', 'promo'],
        ]);

        $this->assertDatabaseHas('referral_codes', [
            'user_id' => $user->id,
            'code' => 'TEST123',
            'is_active' => true,
            'usage_limit' => 100,
            'usage_count' => 0,
            'reward_amount' => 10.50,
            'reward_type' => 'fixed',
            'source' => 'admin',
        ]);

        $this->assertEquals('Test kodas', $referralCode->getTranslation('title', 'lt'));
        $this->assertEquals('Test code', $referralCode->getTranslation('title', 'en'));
        $this->assertEquals(['test', 'promo'], $referralCode->tags);
    }

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $referralCode = ReferralCode::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $referralCode->user);
        $this->assertEquals($user->id, $referralCode->user->id);
    }

    public function test_has_many_referrals(): void
    {
        $referralCode = ReferralCode::factory()->create();
        $referral1 = Referral::factory()->create(['referral_code' => $referralCode->code]);
        $referral2 = Referral::factory()->create(['referral_code' => $referralCode->code]);

        $this->assertCount(2, $referralCode->referrals);
        $this->assertTrue($referralCode->referrals->contains($referral1));
        $this->assertTrue($referralCode->referrals->contains($referral2));
    }

    public function test_has_many_rewards(): void
    {
        $referralCode = ReferralCode::factory()->create();
        $reward1 = ReferralReward::factory()->create(['referral_code' => $referralCode->code]);
        $reward2 = ReferralReward::factory()->create(['referral_code' => $referralCode->code]);

        $this->assertCount(2, $referralCode->rewards);
        $this->assertTrue($referralCode->rewards->contains($reward1));
        $this->assertTrue($referralCode->rewards->contains($reward2));
    }

    public function test_belongs_to_campaign(): void
    {
        $campaign = ReferralCampaign::factory()->create();
        $referralCode = ReferralCode::factory()->create(['campaign_id' => $campaign->id]);

        $this->assertInstanceOf(ReferralCampaign::class, $referralCode->campaign);
        $this->assertEquals($campaign->id, $referralCode->campaign->id);
    }

    public function test_has_many_usage_logs(): void
    {
        $referralCode = ReferralCode::factory()->create();
        $log1 = ReferralCodeUsageLog::factory()->create(['referral_code_id' => $referralCode->id]);
        $log2 = ReferralCodeUsageLog::factory()->create(['referral_code_id' => $referralCode->id]);

        $this->assertCount(2, $referralCode->usageLogs);
        $this->assertTrue($referralCode->usageLogs->contains($log1));
        $this->assertTrue($referralCode->usageLogs->contains($log2));
    }

    public function test_has_many_statistics(): void
    {
        $referralCode = ReferralCode::factory()->create();
        $stat1 = ReferralCodeStatistics::factory()->create(['referral_code_id' => $referralCode->id]);
        $stat2 = ReferralCodeStatistics::factory()->create(['referral_code_id' => $referralCode->id]);

        $this->assertCount(2, $referralCode->statistics);
        $this->assertTrue($referralCode->statistics->contains($stat1));
        $this->assertTrue($referralCode->statistics->contains($stat2));
    }

    public function test_active_scope(): void
    {
        $activeCode = ReferralCode::factory()->create(['is_active' => true]);
        $inactiveCode = ReferralCode::factory()->create(['is_active' => false]);

        $activeCodes = ReferralCode::active()->get();

        $this->assertTrue($activeCodes->contains($activeCode));
        $this->assertFalse($activeCodes->contains($inactiveCode));
    }

    public function test_expired_scope(): void
    {
        $expiredCode = ReferralCode::factory()->create([
            'is_active' => false,
            'expires_at' => now()->subDay(),
        ]);
        $activeCode = ReferralCode::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $expiredCodes = ReferralCode::expired()->get();

        $this->assertTrue($expiredCodes->contains($expiredCode));
        $this->assertFalse($expiredCodes->contains($activeCode));
    }

    public function test_with_usage_limit_scope(): void
    {
        $limitedCode = ReferralCode::factory()->create(['usage_limit' => 100]);
        $unlimitedCode = ReferralCode::factory()->create(['usage_limit' => null]);

        $limitedCodes = ReferralCode::withUsageLimit()->get();

        $this->assertTrue($limitedCodes->contains($limitedCode));
        $this->assertFalse($limitedCodes->contains($unlimitedCode));
    }

    public function test_by_campaign_scope(): void
    {
        $campaign = ReferralCampaign::factory()->create();
        $codeInCampaign = ReferralCode::factory()->create(['campaign_id' => $campaign->id]);
        $codeNotInCampaign = ReferralCode::factory()->create(['campaign_id' => null]);

        $campaignCodes = ReferralCode::byCampaign($campaign->id)->get();

        $this->assertTrue($campaignCodes->contains($codeInCampaign));
        $this->assertFalse($campaignCodes->contains($codeNotInCampaign));
    }

    public function test_by_source_scope(): void
    {
        $adminCode = ReferralCode::factory()->create(['source' => 'admin']);
        $userCode = ReferralCode::factory()->create(['source' => 'user']);

        $adminCodes = ReferralCode::bySource('admin')->get();

        $this->assertTrue($adminCodes->contains($adminCode));
        $this->assertFalse($adminCodes->contains($userCode));
    }

    public function test_by_reward_type_scope(): void
    {
        $fixedCode = ReferralCode::factory()->create(['reward_type' => 'fixed']);
        $percentageCode = ReferralCode::factory()->create(['reward_type' => 'percentage']);

        $fixedCodes = ReferralCode::byRewardType('fixed')->get();

        $this->assertTrue($fixedCodes->contains($fixedCode));
        $this->assertFalse($fixedCodes->contains($percentageCode));
    }

    public function test_is_valid_method(): void
    {
        $validCode = ReferralCode::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
            'usage_limit' => 100,
            'usage_count' => 50,
        ]);

        $this->assertTrue($validCode->isValid());

        // Test inactive code
        $invalidCode = ReferralCode::factory()->create(['is_active' => false]);
        $this->assertFalse($invalidCode->isValid());

        // Test expired code
        $expiredCode = ReferralCode::factory()->create([
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);
        $this->assertFalse($expiredCode->isValid());

        // Test usage limit reached
        $limitReachedCode = ReferralCode::factory()->create([
            'is_active' => true,
            'usage_limit' => 100,
            'usage_count' => 100,
        ]);
        $this->assertFalse($limitReachedCode->isValid());
    }

    public function test_has_reached_usage_limit(): void
    {
        $limitedCode = ReferralCode::factory()->create([
            'usage_limit' => 100,
            'usage_count' => 100,
        ]);

        $unlimitedCode = ReferralCode::factory()->create([
            'usage_limit' => null,
            'usage_count' => 50,
        ]);

        $this->assertTrue($limitedCode->hasReachedUsageLimit());
        $this->assertFalse($unlimitedCode->hasReachedUsageLimit());
    }

    public function test_remaining_usage_attribute(): void
    {
        $limitedCode = ReferralCode::factory()->create([
            'usage_limit' => 100,
            'usage_count' => 30,
        ]);

        $unlimitedCode = ReferralCode::factory()->create([
            'usage_limit' => null,
            'usage_count' => 50,
        ]);

        $this->assertEquals(70, $limitedCode->remaining_usage);
        $this->assertNull($unlimitedCode->remaining_usage);
    }

    public function test_usage_percentage_attribute(): void
    {
        $limitedCode = ReferralCode::factory()->create([
            'usage_limit' => 100,
            'usage_count' => 30,
        ]);

        $unlimitedCode = ReferralCode::factory()->create([
            'usage_limit' => null,
            'usage_count' => 50,
        ]);

        $this->assertEquals(30.0, $limitedCode->usage_percentage);
        $this->assertNull($unlimitedCode->usage_percentage);
    }

    public function test_deactivate_method(): void
    {
        $referralCode = ReferralCode::factory()->create(['is_active' => true]);

        $referralCode->deactivate();

        $this->assertFalse($referralCode->fresh()->is_active);
    }

    public function test_find_by_code(): void
    {
        $referralCode = ReferralCode::factory()->create(['code' => 'UNIQUE123']);

        $found = ReferralCode::findByCode('UNIQUE123');
        $notFound = ReferralCode::findByCode('NOTFOUND');

        $this->assertEquals($referralCode->id, $found->id);
        $this->assertNull($notFound);
    }

    public function test_generate_unique_code(): void
    {
        $code1 = ReferralCode::generateUniqueCode();
        $code2 = ReferralCode::generateUniqueCode();

        $this->assertNotEquals($code1, $code2);
        $this->assertEquals(8, strlen($code1));
        $this->assertEquals(8, strlen($code2));
        $this->assertTrue(ctype_alnum($code1));
        $this->assertTrue(ctype_alnum($code2));
        $this->assertEquals(strtoupper($code1), $code1);
        $this->assertEquals(strtoupper($code2), $code2);
    }

    public function test_referral_url_attribute(): void
    {
        $referralCode = ReferralCode::factory()->create(['code' => 'TEST123']);

        $expectedUrl = url('/register?ref=TEST123');
        $this->assertEquals($expectedUrl, $referralCode->referral_url);
    }

    public function test_increment_usage(): void
    {
        $user = User::factory()->create();
        $referralCode = ReferralCode::factory()->create(['usage_count' => 5]);

        $this->actingAs($user);
        
        $referralCode->incrementUsage();

        $this->assertEquals(6, $referralCode->fresh()->usage_count);
        $this->assertDatabaseHas('referral_code_usage_logs', [
            'referral_code_id' => $referralCode->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_localized_title_attribute(): void
    {
        app()->setLocale('lt');
        $referralCode = ReferralCode::factory()->create([
            'title' => ['lt' => 'Lietuviškas pavadinimas', 'en' => 'English title'],
        ]);

        $this->assertEquals('Lietuviškas pavadinimas', $referralCode->localized_title);

        app()->setLocale('en');
        $this->assertEquals('English title', $referralCode->localized_title);
    }

    public function test_localized_description_attribute(): void
    {
        app()->setLocale('lt');
        $referralCode = ReferralCode::factory()->create([
            'description' => ['lt' => 'Lietuviškas aprašymas', 'en' => 'English description'],
        ]);

        $this->assertEquals('Lietuviškas aprašymas', $referralCode->localized_description);

        app()->setLocale('en');
        $this->assertEquals('English description', $referralCode->localized_description);
    }

    public function test_formatted_reward_amount_attribute(): void
    {
        $referralCode = ReferralCode::factory()->create(['reward_amount' => 15.75]);
        $noRewardCode = ReferralCode::factory()->create(['reward_amount' => null]);

        $this->assertEquals('15.75 EUR', $referralCode->formatted_reward_amount);
        $this->assertNull($noRewardCode->formatted_reward_amount);
    }

    public function test_meets_conditions(): void
    {
        $referralCode = ReferralCode::factory()->create([
            'conditions' => [
                ['field' => 'min_order_amount', 'operator' => '>=', 'value' => 50],
                ['field' => 'user_type', 'operator' => '=', 'value' => 'premium'],
            ],
        ]);

        $validContext = [
            'min_order_amount' => 100,
            'user_type' => 'premium',
        ];

        $invalidContext = [
            'min_order_amount' => 30,
            'user_type' => 'basic',
        ];

        $this->assertTrue($referralCode->meetsConditions($validContext));
        $this->assertFalse($referralCode->meetsConditions($invalidContext));
    }

    public function test_display_data_attribute(): void
    {
        $referralCode = ReferralCode::factory()->create([
            'title' => ['lt' => 'Test pavadinimas', 'en' => 'Test title'],
            'description' => ['lt' => 'Test aprašymas', 'en' => 'Test description'],
            'reward_amount' => 25.50,
            'reward_type' => 'fixed',
            'tags' => ['test', 'promo'],
        ]);

        $displayData = $referralCode->display_data;

        $this->assertIsArray($displayData);
        $this->assertEquals($referralCode->id, $displayData['id']);
        $this->assertEquals($referralCode->code, $displayData['code']);
        $this->assertEquals('Test pavadinimas', $displayData['title']);
        $this->assertEquals('Test aprašymas', $displayData['description']);
        $this->assertEquals(25.50, $displayData['reward_amount']);
        $this->assertEquals('fixed', $displayData['reward_type']);
        $this->assertEquals('25.50 EUR', $displayData['formatted_reward_amount']);
        $this->assertEquals(['test', 'promo'], $displayData['tags']);
    }

    public function test_stats_attribute(): void
    {
        $referralCode = ReferralCode::factory()->create();
        
        // Create some referrals
        Referral::factory()->count(3)->create(['referral_code' => $referralCode->code]);
        Referral::factory()->count(2)->create([
            'referral_code' => $referralCode->code,
            'status' => 'completed',
        ]);

        // Create some rewards
        ReferralReward::factory()->count(2)->create([
            'referral_code' => $referralCode->code,
            'amount' => 10.50,
        ]);

        $stats = $referralCode->stats;

        $this->assertIsArray($stats);
        $this->assertEquals(5, $stats['total_referrals']);
        $this->assertEquals(2, $stats['completed_referrals']);
        $this->assertEquals(3, $stats['pending_referrals']);
        $this->assertEquals(2, $stats['total_rewards']);
        $this->assertEquals(21.00, $stats['total_reward_amount']);
    }
}

