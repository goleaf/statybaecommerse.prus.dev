<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReferralModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_belongs_to_referrer(): void
    {
        $referrer = User::factory()->create();
        $referral = Referral::factory()->create(['referrer_id' => $referrer->id]);

        $this->assertEquals($referrer->id, $referral->referrer->id);
    }

    public function test_referral_belongs_to_referred(): void
    {
        $referred = User::factory()->create();
        $referral = Referral::factory()->create(['referred_id' => $referred->id]);

        $this->assertEquals($referred->id, $referral->referred->id);
    }

    public function test_referral_has_many_rewards(): void
    {
        $referral = Referral::factory()->create();
        $reward1 = ReferralReward::factory()->create(['referral_id' => $referral->id]);
        $reward2 = ReferralReward::factory()->create(['referral_id' => $referral->id]);

        $this->assertCount(2, $referral->rewards);
        $this->assertTrue($referral->rewards->contains($reward1));
        $this->assertTrue($referral->rewards->contains($reward2));
    }

    public function test_referral_find_by_code(): void
    {
        $referral = Referral::factory()->create(['referral_code' => 'TEST123']);

        $found = Referral::findByCode('TEST123');

        $this->assertEquals($referral->id, $found->id);
    }

    public function test_referral_find_by_code_returns_null_for_invalid(): void
    {
        $found = Referral::findByCode('INVALID');

        $this->assertNull($found);
    }

    public function test_user_already_referred_check(): void
    {
        $user = User::factory()->create();
        Referral::factory()->create(['referred_id' => $user->id]);

        $this->assertTrue(Referral::userAlreadyReferred($user->id));
    }

    public function test_user_not_referred_check(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(Referral::userAlreadyReferred($user->id));
    }

    public function test_can_user_refer_within_limit(): void
    {
        $user = User::factory()->create();
        
        // Create 5 active referrals (under limit of 100)
        Referral::factory()->count(5)->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertTrue(Referral::canUserRefer($user->id));
    }

    public function test_cannot_user_refer_over_limit(): void
    {
        $user = User::factory()->create();
        
        // Create 101 active referrals (over limit of 100)
        Referral::factory()->count(101)->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertFalse(Referral::canUserRefer($user->id));
    }

    public function test_referral_is_valid_when_pending_and_not_expired(): void
    {
        $referral = Referral::factory()->create([
            'status' => 'pending',
            'expires_at' => now()->addDay(),
        ]);

        $this->assertTrue($referral->isValid());
    }

    public function test_referral_is_invalid_when_completed(): void
    {
        $referral = Referral::factory()->create(['status' => 'completed']);

        $this->assertFalse($referral->isValid());
    }

    public function test_referral_is_invalid_when_expired(): void
    {
        $referral = Referral::factory()->create([
            'status' => 'pending',
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($referral->isValid());
    }

    public function test_referral_is_about_to_expire(): void
    {
        $referral = Referral::factory()->create([
            'expires_at' => now()->addDays(3),
        ]);

        $this->assertTrue($referral->isAboutToExpire());
    }

    public function test_referral_is_not_about_to_expire(): void
    {
        $referral = Referral::factory()->create([
            'expires_at' => now()->addDays(10),
        ]);

        $this->assertFalse($referral->isAboutToExpire());
    }

    public function test_referral_performance_score_calculation(): void
    {
        $referral = Referral::factory()->create(['status' => 'completed']);
        ReferralReward::factory()->create(['referral_id' => $referral->id]);

        $score = $referral->performance_score;

        $this->assertGreaterThan(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_referral_total_rewards_amount(): void
    {
        $referral = Referral::factory()->create();
        ReferralReward::factory()->create([
            'referral_id' => $referral->id,
            'amount' => 10.50,
        ]);
        ReferralReward::factory()->create([
            'referral_id' => $referral->id,
            'amount' => 5.25,
        ]);

        $this->assertEquals(15.75, $referral->total_rewards_amount);
    }

    public function test_referral_days_since_created(): void
    {
        $referral = Referral::factory()->create([
            'created_at' => now()->subDays(5),
        ]);

        $this->assertEquals(5, $referral->days_since_created);
    }

    public function test_referral_create_with_code(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::createWithCode([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);

        $this->assertNotNull($referral->referral_code);
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);
    }

    public function test_referral_create_with_custom_code(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::createWithCode([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'referral_code' => 'CUSTOM123',
        ]);

        $this->assertEquals('CUSTOM123', $referral->referral_code);
    }

    public function test_referral_scope_by_source(): void
    {
        Referral::factory()->create(['source' => 'email']);
        Referral::factory()->create(['source' => 'social']);
        Referral::factory()->create(['source' => 'email']);

        $emailReferrals = Referral::bySource('email')->get();

        $this->assertCount(2, $emailReferrals);
    }

    public function test_referral_scope_by_campaign(): void
    {
        Referral::factory()->create(['campaign' => 'summer2024']);
        Referral::factory()->create(['campaign' => 'winter2024']);
        Referral::factory()->create(['campaign' => 'summer2024']);

        $summerReferrals = Referral::byCampaign('summer2024')->get();

        $this->assertCount(2, $summerReferrals);
    }

    public function test_referral_scope_with_rewards(): void
    {
        $referral1 = Referral::factory()->create();
        $referral2 = Referral::factory()->create();
        ReferralReward::factory()->create(['referral_id' => $referral1->id]);

        $referralsWithRewards = Referral::withRewards()->get();

        $this->assertCount(1, $referralsWithRewards);
        $this->assertTrue($referralsWithRewards->contains($referral1));
    }

    public function test_referral_scope_without_rewards(): void
    {
        $referral1 = Referral::factory()->create();
        $referral2 = Referral::factory()->create();
        ReferralReward::factory()->create(['referral_id' => $referral1->id]);

        $referralsWithoutRewards = Referral::withoutRewards()->get();

        $this->assertCount(1, $referralsWithoutRewards);
        $this->assertTrue($referralsWithoutRewards->contains($referral2));
    }
}