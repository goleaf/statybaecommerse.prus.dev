<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\User;
use App\Services\ReferralCodeService;
use App\Services\ReferralRewardService;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReferralSystemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $referrer;

    private User $referred;

    private ReferralService $referralService;

    private ReferralCodeService $referralCodeService;

    private ReferralRewardService $referralRewardService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referrer = User::factory()->create([
            'name'  => 'John Referrer',
            'email' => 'referrer@example.com',
        ]);

        $this->referred = User::factory()->create([
            'name'  => 'Jane Referred',
            'email' => 'referred@example.com',
        ]);

        $this->referralService = app(ReferralService::class);
        $this->referralCodeService = app(ReferralCodeService::class);
        $this->referralRewardService = app(ReferralRewardService::class);
    }

    #[Test]
    public function it_can_generate_referral_code_for_user(): void
    {
        $referralCode = $this->referralService->generateReferralCodeForUser($this->referrer->id);

        $this->assertNotNull($referralCode);
        $this->assertEquals($this->referrer->id, $referralCode->user_id);
        $this->assertTrue($referralCode->is_active);
        $this->assertNotNull($referralCode->code);
        $this->assertEquals(8, strlen($referralCode->code));
    }

    #[Test]
    public function it_can_create_referral_relationship(): void
    {
        $referral = $this->referralService->createReferral(
            $this->referrer->id,
            $this->referred->id
        );

        $this->assertNotNull($referral);
        $this->assertEquals($this->referrer->id, $referral->referrer_id);
        $this->assertEquals($this->referred->id, $referral->referred_id);
        $this->assertEquals('pending', $referral->status);
        $this->assertNotNull($referral->referral_code);
    }

    #[Test]
    public function it_can_process_referral_completion(): void
    {
        // Create referral first
        $referral = $this->referralService->createReferral(
            $this->referrer->id,
            $this->referred->id
        );

        // Create a mock order
        $order = Order::factory()->create([
            'user_id' => $this->referred->id,
        ]);

        // Process referral completion
        $result = $this->referralService->processReferralCompletion(
            $this->referred->id,
            $order->id
        );

        $this->assertTrue($result);

        // Check referral is completed
        $referral->refresh();
        $this->assertEquals('completed', $referral->status);
        $this->assertNotNull($referral->completed_at);

        // Check rewards were created
        $rewards = ReferralReward::where('referral_id', $referral->id)->get();
        $this->assertCount(2, $rewards); // Discount for referred user + tiered credit for referrer

        $discountReward = $rewards->firstWhere('type', 'referred_discount');
        $this->assertEquals('referred_discount', $discountReward->type);
        $this->assertEquals(5.0, $discountReward->amount);
        $this->assertEquals('applied', $discountReward->status);
        $this->assertEquals('discount', $discountReward->reward_category);

        $tierReward = $rewards->firstWhere('type', 'referrer_bonus');
        $this->assertNotNull($tierReward);
        $this->assertEquals('credit', $tierReward->reward_category);
        $this->assertEquals(5.0, $tierReward->amount);
        $this->assertEquals('pending', $tierReward->status);
    }

    #[Test]
    public function it_can_validate_referral_code(): void
    {
        // Generate referral code
        $referralCode = $this->referralService->generateReferralCodeForUser($this->referrer->id);

        // Validate the code
        $user = $this->referralService->validateReferralCode($referralCode->code);

        $this->assertNotNull($user);
        $this->assertEquals($this->referrer->id, $user->id);
    }

    #[Test]
    public function it_rejects_invalid_referral_code(): void
    {
        $user = $this->referralService->validateReferralCode('INVALID123');

        $this->assertNull($user);
    }

    #[Test]
    public function it_prevents_duplicate_referrals(): void
    {
        // Create first referral
        $this->referralService->createReferral(
            $this->referrer->id,
            $this->referred->id
        );

        // Try to create duplicate referral
        $duplicateReferral = $this->referralService->createReferral(
            $this->referrer->id,
            $this->referred->id
        );

        $this->assertNull($duplicateReferral);
    }

    #[Test]
    public function it_can_get_referral_statistics(): void
    {
        // Create some referrals
        $this->referralService->createReferral(
            $this->referrer->id,
            $this->referred->id
        );

        $stats = $this->referralService->getUserReferralStats($this->referrer->id);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_referrals', $stats);
        $this->assertArrayHasKey('completed_referrals', $stats);
        $this->assertArrayHasKey('pending_referrals', $stats);
        $this->assertEquals(1, $stats['total_referrals']);
        $this->assertEquals(0, $stats['completed_referrals']);
        $this->assertEquals(1, $stats['pending_referrals']);
    }

    #[Test]
    public function it_can_get_referral_dashboard_data(): void
    {
        // Generate referral code
        $this->referralService->generateReferralCodeForUser($this->referrer->id);

        // Create referral
        $this->referralService->createReferral(
            $this->referrer->id,
            $this->referred->id
        );

        $dashboardData = $this->referralService->getReferralDashboardData($this->referrer->id);

        $this->assertIsArray($dashboardData);
        $this->assertArrayHasKey('stats', $dashboardData);
        $this->assertArrayHasKey('recent_referrals', $dashboardData);
        $this->assertArrayHasKey('pending_rewards', $dashboardData);
        $this->assertArrayHasKey('referral_code', $dashboardData);
        $this->assertArrayHasKey('referral_url', $dashboardData);
    }

    #[Test]
    public function it_can_create_referral_rewards(): void
    {
        $referral = $this->referralService->createReferral(
            $this->referrer->id,
            $this->referred->id
        );

        $order = Order::factory()->create([
            'user_id' => $this->referred->id,
        ]);

        // Create referred discount
        $discountReward = $this->referralRewardService->createReferredDiscount(
            $referral->id,
            $this->referred->id,
            $order->id,
            5.0
        );

        $this->assertNotNull($discountReward);
        $this->assertEquals('referred_discount', $discountReward->type);
        $this->assertEquals(5.0, $discountReward->amount);
        $this->assertEquals('applied', $discountReward->status);
        $this->assertEquals('discount', $discountReward->reward_category);
    }

    #[Test]
    public function it_can_validate_referral_code_format(): void
    {
        $this->assertTrue($this->referralCodeService->validateCodeFormat('ABC12345'));
        $this->assertTrue($this->referralCodeService->validateCodeFormat('12345678'));
        $this->assertFalse($this->referralCodeService->validateCodeFormat('abc123')); // lowercase
        $this->assertFalse($this->referralCodeService->validateCodeFormat('ABC-123')); // special chars
        $this->assertFalse($this->referralCodeService->validateCodeFormat('ABC')); // too short
    }

    #[Test]
    public function it_can_generate_unique_referral_codes(): void
    {
        $code1 = $this->referralCodeService->generateUniqueCode();
        $code2 = $this->referralCodeService->generateUniqueCode();

        $this->assertNotEquals($code1, $code2);
        $this->assertEquals(8, strlen($code1));
        $this->assertEquals(8, strlen($code2));
    }

    #[Test]
    public function it_can_get_referral_url(): void
    {
        $code = 'TEST1234';
        $url = $this->referralCodeService->getReferralUrl($code);

        $this->assertStringContainsString('ref=TEST1234', $url);
        $this->assertStringContainsString('/register', $url);
    }

    #[Test]
    public function it_can_generate_social_share_links(): void
    {
        $code = 'TESTCODE';
        $links = $this->referralCodeService->getShareLinks($code, 'Join me');

        $this->assertArrayHasKey('facebook', $links);
        $this->assertArrayHasKey('twitter', $links);
        $this->assertArrayHasKey('linkedin', $links);
        $this->assertArrayHasKey('email', $links);
        $this->assertStringContainsString(urlencode('Join me'), $links['email']);
        $this->assertStringContainsString(urlencode($this->referralCodeService->getReferralUrl($code)), $links['facebook']);
    }

    #[Test]
    public function it_can_extract_code_from_url(): void
    {
        $url = 'https://example.com/register?ref=TEST1234';
        $code = $this->referralCodeService->extractCodeFromUrl($url);

        $this->assertEquals('TEST1234', $code);
    }

    #[Test]
    public function it_can_cleanup_expired_referrals(): void
    {
        // Create expired referral
        $referral = Referral::factory()->create([
            'referrer_id' => $this->referrer->id,
            'referred_id' => $this->referred->id,
            'status'      => 'pending',
            'expires_at'  => now()->subDay(),
        ]);

        $count = $this->referralService->cleanupExpiredReferrals();

        $this->assertEquals(1, $count);

        $referral->refresh();
        $this->assertEquals('expired', $referral->status);
    }

    #[Test]
    public function it_can_cleanup_expired_rewards(): void
    {
        $referral = Referral::factory()->create([
            'referrer_id' => $this->referrer->id,
            'referred_id' => $this->referred->id,
        ]);

        // Create expired reward
        $reward = ReferralReward::factory()->create([
            'referral_id' => $referral->id,
            'user_id'     => $this->referred->id,
            'status'      => 'pending',
            'expires_at'  => now()->subDay(),
        ]);

        $count = $this->referralRewardService->cleanupExpiredRewards();

        $this->assertEquals(1, $count);

        $reward->refresh();
        $this->assertEquals('expired', $reward->status);
    }

    #[Test]
    public function it_can_check_if_user_can_use_referral_discount(): void
    {
        // Initially, user cannot use referral discount
        $this->assertFalse($this->referralRewardService->canUserUseReferralDiscount($this->referred->id));

        // Create completed referral
        $referral = $this->referralService->createReferral(
            $this->referrer->id,
            $this->referred->id
        );

        $order = Order::factory()->create([
            'user_id' => $this->referred->id,
        ]);

        $this->referralService->processReferralCompletion(
            $this->referred->id,
            $order->id
        );

        // Now user can use referral discount
        $this->assertTrue($this->referralRewardService->canUserUseReferralDiscount($this->referred->id));
    }

    #[Test]
    public function it_reuses_referral_codes_for_multiple_referrals(): void
    {
        $code = $this->referralService->generateReferralCodeForUser($this->referrer->id);

        $firstReferral = $this->referralService->createReferral($this->referrer->id, $this->referred->id, $code->code);
        $secondReferred = User::factory()->create();
        $secondReferral = $this->referralService->createReferral($this->referrer->id, $secondReferred->id, $code->code);

        $this->assertNotNull($firstReferral);
        $this->assertNotNull($secondReferral);
        $this->assertEquals($code->code, $firstReferral->referral_code);
        $this->assertEquals($code->code, $secondReferral->referral_code);
    }

    #[Test]
    public function it_records_referral_attribution_context(): void
    {
        $context = [
            'source'      => 'newsletter',
            'campaign'    => 'spring_launch',
            'utm_source'  => 'email',
            'utm_medium'  => 'campaign',
            'utm_campaign'=> 'spring2025',
            'metadata'    => ['landing_page' => '/promo'],
        ];

        $referral = $this->referralService->createReferral($this->referrer->id, $this->referred->id, null, $context);

        $this->assertEquals('newsletter', $referral->source);
        $this->assertEquals('spring_launch', $referral->campaign);
        $this->assertEquals('email', $referral->utm_source);
        $this->assertEquals('campaign', $referral->utm_medium);
        $this->assertEquals('spring2025', $referral->utm_campaign);
        $this->assertEquals(['landing_page' => '/promo'], $referral->metadata);
    }

    #[Test]
    public function it_grants_tiered_rewards_as_referrals_complete(): void
    {
        $code = $this->referralService->generateReferralCodeForUser($this->referrer->id);
        $totalReferrals = 6;
        for ($i = 0; $i < $totalReferrals; $i++) {
            $newUser = User::factory()->create();
            $referral = $this->referralService->createReferral($this->referrer->id, $newUser->id, $code->code);
            $order = Order::factory()->create(['user_id' => $newUser->id]);
            $this->referralService->processReferralCompletion($newUser->id, $order->id);
        }

        $rewards = ReferralReward::where('user_id', $this->referrer->id)->referrerBonus()->get();

        $this->assertNotEmpty($rewards);
        $latestReward = $rewards->last();
        $this->assertEquals('credit', $latestReward->reward_category);
        $this->assertEquals(10.0, $latestReward->amount); // Tier after 5 completions
        $this->assertEquals(5, $latestReward->reward_data['tier_threshold']);
    }

    #[Test]
    public function it_can_get_total_rewards_value(): void
    {
        $referral = Referral::factory()->create([
            'referrer_id' => $this->referrer->id,
            'referred_id' => $this->referred->id,
        ]);

        // Create some rewards
        ReferralReward::factory()->create([
            'referral_id' => $referral->id,
            'user_id'     => $this->referrer->id,
            'type'        => 'referrer_bonus',
            'amount'      => 10.0,
            'status'      => 'pending',
        ]);

        ReferralReward::factory()->create([
            'referral_id' => $referral->id,
            'user_id'     => $this->referrer->id,
            'type'        => 'referrer_bonus',
            'amount'      => 5.0,
            'status'      => 'applied',
        ]);

        $totals = $this->referralRewardService->getTotalRewardsValue($this->referrer->id);

        $this->assertEquals(10.0, $totals['pending']);
        $this->assertEquals(5.0, $totals['applied']);
        $this->assertEquals(15.0, $totals['total']);
    }
}
