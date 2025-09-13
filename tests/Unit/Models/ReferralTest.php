<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Referral;
use App\Models\User;
use App\Models\Order;
use App\Models\ReferralReward;
use App\Models\AnalyticsEvent;
use App\Models\Translations\ReferralTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_can_be_created(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'referral_code' => 'TEST123',
            'status' => 'pending',
            'title' => ['en' => 'Test Referral', 'lt' => 'Testo Rekomendacija'],
            'description' => ['en' => 'Test Description', 'lt' => 'Testo Aprašymas'],
        ]);

        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'referral_code' => 'TEST123',
            'status' => 'pending',
        ]);

        $this->assertEquals('Test Referral', $referral->getTranslation('title', 'en'));
        $this->assertEquals('Testo Rekomendacija', $referral->getTranslation('title', 'lt'));
    }

    public function test_referral_belongs_to_referrer(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);

        $this->assertInstanceOf(User::class, $referral->referrer);
        $this->assertEquals($referrer->id, $referral->referrer->id);
    }

    public function test_referral_belongs_to_referred(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);

        $this->assertInstanceOf(User::class, $referral->referred);
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

    public function test_referral_has_many_analytics_events(): void
    {
        $referral = Referral::factory()->create();
        
        $event1 = AnalyticsEvent::factory()->create(['referral_id' => $referral->id]);
        $event2 = AnalyticsEvent::factory()->create(['referral_id' => $referral->id]);

        $this->assertCount(2, $referral->analyticsEvents);
        $this->assertTrue($referral->analyticsEvents->contains($event1));
        $this->assertTrue($referral->analyticsEvents->contains($event2));
    }

    public function test_referral_has_many_referred_orders(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);

        $order1 = Order::factory()->create(['user_id' => $referred->id]);
        $order2 = Order::factory()->create(['user_id' => $referred->id]);

        $this->assertCount(2, $referral->referredOrders);
        $this->assertTrue($referral->referredOrders->contains($order1));
        $this->assertTrue($referral->referredOrders->contains($order2));
    }

    public function test_referral_has_many_translations(): void
    {
        $referral = Referral::factory()->create();
        
        $translation1 = ReferralTranslation::factory()->create(['referral_id' => $referral->id, 'locale' => 'en']);
        $translation2 = ReferralTranslation::factory()->create(['referral_id' => $referral->id, 'locale' => 'lt']);

        $this->assertCount(2, $referral->translations);
        $this->assertTrue($referral->translations->contains($translation1));
        $this->assertTrue($referral->translations->contains($translation2));
    }

    public function test_active_scope(): void
    {
        $activeReferral = Referral::factory()->create([
            'status' => 'pending',
            'expires_at' => now()->addDays(30),
        ]);

        $expiredReferral = Referral::factory()->create([
            'status' => 'expired',
        ]);

        $expiredByDateReferral = Referral::factory()->create([
            'status' => 'pending',
            'expires_at' => now()->subDays(1),
        ]);

        $activeReferrals = Referral::active()->get();

        $this->assertCount(1, $activeReferrals);
        $this->assertTrue($activeReferrals->contains($activeReferral));
        $this->assertFalse($activeReferrals->contains($expiredReferral));
        $this->assertFalse($activeReferrals->contains($expiredByDateReferral));
    }

    public function test_completed_scope(): void
    {
        $completedReferral = Referral::factory()->create(['status' => 'completed']);
        $pendingReferral = Referral::factory()->create(['status' => 'pending']);

        $completedReferrals = Referral::completed()->get();

        $this->assertCount(1, $completedReferrals);
        $this->assertTrue($completedReferrals->contains($completedReferral));
        $this->assertFalse($completedReferrals->contains($pendingReferral));
    }

    public function test_expired_scope(): void
    {
        $expiredReferral = Referral::factory()->create(['status' => 'expired']);
        $expiredByDateReferral = Referral::factory()->create([
            'status' => 'pending',
            'expires_at' => now()->subDays(1),
        ]);
        $activeReferral = Referral::factory()->create([
            'status' => 'pending',
            'expires_at' => now()->addDays(30),
        ]);

        $expiredReferrals = Referral::expired()->get();

        $this->assertCount(2, $expiredReferrals);
        $this->assertTrue($expiredReferrals->contains($expiredReferral));
        $this->assertTrue($expiredReferrals->contains($expiredByDateReferral));
        $this->assertFalse($expiredReferrals->contains($activeReferral));
    }

    public function test_is_valid_method(): void
    {
        $validReferral = Referral::factory()->create([
            'status' => 'pending',
            'expires_at' => now()->addDays(30),
        ]);

        $expiredReferral = Referral::factory()->create([
            'status' => 'pending',
            'expires_at' => now()->subDays(1),
        ]);

        $completedReferral = Referral::factory()->create([
            'status' => 'completed',
        ]);

        $this->assertTrue($validReferral->isValid());
        $this->assertFalse($expiredReferral->isValid());
        $this->assertFalse($completedReferral->isValid());
    }

    public function test_mark_as_completed(): void
    {
        $referral = Referral::factory()->create(['status' => 'pending']);

        $referral->markAsCompleted();

        $this->assertEquals('completed', $referral->fresh()->status);
        $this->assertNotNull($referral->fresh()->completed_at);
    }

    public function test_mark_as_expired(): void
    {
        $referral = Referral::factory()->create(['status' => 'pending']);

        $referral->markAsExpired();

        $this->assertEquals('expired', $referral->fresh()->status);
    }

    public function test_find_by_code(): void
    {
        $referral = Referral::factory()->create(['referral_code' => 'UNIQUE123']);

        $foundReferral = Referral::findByCode('UNIQUE123');
        $notFoundReferral = Referral::findByCode('NOTFOUND');

        $this->assertEquals($referral->id, $foundReferral->id);
        $this->assertNull($notFoundReferral);
    }

    public function test_user_already_referred(): void
    {
        $user = User::factory()->create();
        $referrer = User::factory()->create();

        $this->assertFalse(Referral::userAlreadyReferred($user->id));

        Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $user->id,
        ]);

        $this->assertTrue(Referral::userAlreadyReferred($user->id));
    }

    public function test_can_user_refer(): void
    {
        $user = User::factory()->create();

        // User should be able to refer initially
        $this->assertTrue(Referral::canUserRefer($user->id));

        // Create 100 active referrals
        for ($i = 0; $i < 100; $i++) {
            $referred = User::factory()->create();
            Referral::factory()->create([
                'referrer_id' => $user->id,
                'referred_id' => $referred->id,
                'status' => 'pending',
            ]);
        }

        // User should not be able to refer more
        $this->assertFalse(Referral::canUserRefer($user->id));
    }

    public function test_by_source_scope(): void
    {
        $websiteReferral = Referral::factory()->create(['source' => 'website']);
        $emailReferral = Referral::factory()->create(['source' => 'email']);

        $websiteReferrals = Referral::bySource('website')->get();

        $this->assertCount(1, $websiteReferrals);
        $this->assertTrue($websiteReferrals->contains($websiteReferral));
        $this->assertFalse($websiteReferrals->contains($emailReferral));
    }

    public function test_by_campaign_scope(): void
    {
        $summerReferral = Referral::factory()->create(['campaign' => 'summer2024']);
        $winterReferral = Referral::factory()->create(['campaign' => 'winter2024']);

        $summerReferrals = Referral::byCampaign('summer2024')->get();

        $this->assertCount(1, $summerReferrals);
        $this->assertTrue($summerReferrals->contains($summerReferral));
        $this->assertFalse($summerReferrals->contains($winterReferral));
    }

    public function test_with_rewards_scope(): void
    {
        $referralWithRewards = Referral::factory()->create();
        $referralWithoutRewards = Referral::factory()->create();

        ReferralReward::factory()->create(['referral_id' => $referralWithRewards->id]);

        $referralsWithRewards = Referral::withRewards()->get();

        $this->assertCount(1, $referralsWithRewards);
        $this->assertTrue($referralsWithRewards->contains($referralWithRewards));
        $this->assertFalse($referralsWithRewards->contains($referralWithoutRewards));
    }

    public function test_without_rewards_scope(): void
    {
        $referralWithRewards = Referral::factory()->create();
        $referralWithoutRewards = Referral::factory()->create();

        ReferralReward::factory()->create(['referral_id' => $referralWithRewards->id]);

        $referralsWithoutRewards = Referral::withoutRewards()->get();

        $this->assertCount(1, $referralsWithoutRewards);
        $this->assertTrue($referralsWithoutRewards->contains($referralWithoutRewards));
        $this->assertFalse($referralsWithoutRewards->contains($referralWithRewards));
    }

    public function test_total_rewards_amount_attribute(): void
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

    public function test_conversion_rate_attribute(): void
    {
        $referral = Referral::factory()->create();

        // No orders initially
        $this->assertEquals(0.0, $referral->conversion_rate);

        // Add an order
        Order::factory()->create(['user_id' => $referral->referred_id]);

        $this->assertEquals(100.0, $referral->fresh()->conversion_rate);
    }

    public function test_days_since_created_attribute(): void
    {
        $referral = Referral::factory()->create([
            'created_at' => now()->subDays(5),
        ]);

        $this->assertEquals(5, $referral->days_since_created);
    }

    public function test_is_about_to_expire(): void
    {
        $referralAboutToExpire = Referral::factory()->create([
            'expires_at' => now()->addDays(3),
        ]);

        $referralNotExpiring = Referral::factory()->create([
            'expires_at' => now()->addDays(30),
        ]);

        $referralNoExpiry = Referral::factory()->create([
            'expires_at' => null,
        ]);

        $this->assertTrue($referralAboutToExpire->isAboutToExpire());
        $this->assertFalse($referralNotExpiring->isAboutToExpire());
        $this->assertFalse($referralNoExpiry->isAboutToExpire());
    }

    public function test_performance_score_attribute(): void
    {
        $referral = Referral::factory()->create(['status' => 'pending']);

        // Base score for pending referral
        $this->assertEquals(0, $referral->performance_score);

        // Mark as completed
        $referral->markAsCompleted();
        $this->assertEquals(50, $referral->fresh()->performance_score);

        // Add rewards
        ReferralReward::factory()->create(['referral_id' => $referral->id]);
        $this->assertEquals(70, $referral->fresh()->performance_score);

        // Add orders
        Order::factory()->create(['user_id' => $referral->referred_id]);
        $this->assertEquals(75, $referral->fresh()->performance_score);
    }

    public function test_generate_unique_code(): void
    {
        $code1 = Referral::generateUniqueCode();
        $code2 = Referral::generateUniqueCode();

        $this->assertNotEquals($code1, $code2);
        $this->assertEquals(8, strlen($code1));
        $this->assertEquals(8, strlen($code2));
        $this->assertTrue(ctype_upper($code1));
        $this->assertTrue(ctype_upper($code2));
    }

    public function test_create_with_code(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::createWithCode([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'status' => 'pending',
        ]);

        $this->assertNotNull($referral->referral_code);
        $this->assertEquals(8, strlen($referral->referral_code));
    }

    public function test_create_with_code_uses_provided_code(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::createWithCode([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'status' => 'pending',
            'referral_code' => 'CUSTOM123',
        ]);

        $this->assertEquals('CUSTOM123', $referral->referral_code);
    }

    public function test_soft_deletes(): void
    {
        $referral = Referral::factory()->create();

        $referral->delete();

        $this->assertSoftDeleted('referrals', ['id' => $referral->id]);
        $this->assertCount(0, Referral::all());
        $this->assertCount(1, Referral::withTrashed()->get());
    }

    public function test_translatable_fields(): void
    {
        $referral = Referral::factory()->create([
            'title' => ['en' => 'English Title', 'lt' => 'Lietuvių Pavadinimas'],
            'description' => ['en' => 'English Description', 'lt' => 'Lietuvių Aprašymas'],
        ]);

        $this->assertEquals('English Title', $referral->getTranslation('title', 'en'));
        $this->assertEquals('Lietuvių Pavadinimas', $referral->getTranslation('title', 'lt'));
        $this->assertEquals('English Description', $referral->getTranslation('description', 'en'));
        $this->assertEquals('Lietuvių Aprašymas', $referral->getTranslation('description', 'lt'));
    }
}



