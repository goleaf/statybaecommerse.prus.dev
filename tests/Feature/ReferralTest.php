<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_their_referrals(): void
    {
        $user = User::factory()->create();
        $referral = Referral::factory()->create(['referrer_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('referrals.index'));

        $response->assertOk();
        $response->assertSee($referral->referral_code);
    }

    public function test_user_can_create_referral(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $response = $this->actingAs($referrer)->post(route('referrals.store'), [
            'referred_email' => $referred->email,
            'message' => 'Check out this great service!',
        ]);

        $response->assertRedirect(route('referrals.index'));
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);
    }

    public function test_user_cannot_refer_themselves(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('referrals.store'), [
            'referred_email' => $user->email,
        ]);

        $response->assertSessionHasErrors(['referred_email']);
    }

    public function test_user_cannot_refer_already_referred_user(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();
        
        // Create existing referral
        Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);

        $response = $this->actingAs($referrer)->post(route('referrals.store'), [
            'referred_email' => $referred->email,
        ]);

        $response->assertSessionHasErrors(['referred_email']);
    }

    public function test_user_can_generate_referral_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('referrals.generate-code'));

        $response->assertRedirect(route('referrals.index'));
        $this->assertDatabaseHas('referral_codes', [
            'user_id' => $user->id,
            'is_active' => true,
        ]);
    }

    public function test_user_can_view_referral_details(): void
    {
        $user = User::factory()->create();
        $referral = Referral::factory()->create(['referrer_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('referrals.show', $referral));

        $response->assertOk();
        $response->assertSee($referral->referral_code);
    }

    public function test_user_cannot_view_other_users_referrals(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $referral = Referral::factory()->create(['referrer_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('referrals.show', $referral));

        $response->assertForbidden();
    }

    public function test_referral_tracking_works(): void
    {
        $user = User::factory()->create();
        $referralCode = ReferralCode::factory()->create([
            'user_id' => $user->id,
            'code' => 'TEST123',
        ]);

        $response = $this->get(route('referrals.track', 'TEST123'));

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('referral_code', 'TEST123');
    }

    public function test_invalid_referral_code_redirects(): void
    {
        $response = $this->get(route('referrals.track', 'INVALID'));

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('error');
    }

    public function test_referral_can_be_marked_completed(): void
    {
        $referral = Referral::factory()->create(['status' => 'pending']);

        $referral->markAsCompleted();

        $this->assertEquals('completed', $referral->fresh()->status);
        $this->assertNotNull($referral->fresh()->completed_at);
    }

    public function test_referral_can_be_marked_expired(): void
    {
        $referral = Referral::factory()->create(['status' => 'pending']);

        $referral->markAsExpired();

        $this->assertEquals('expired', $referral->fresh()->status);
    }

    public function test_referral_code_generation_is_unique(): void
    {
        $code1 = ReferralCode::generateUniqueCode();
        $code2 = ReferralCode::generateUniqueCode();

        $this->assertNotEquals($code1, $code2);
        $this->assertDatabaseMissing('referral_codes', ['code' => $code1]);
        $this->assertDatabaseMissing('referral_codes', ['code' => $code2]);
    }

    public function test_referral_rewards_relationship(): void
    {
        $referral = Referral::factory()->create();
        $reward = ReferralReward::factory()->create(['referral_id' => $referral->id]);

        $this->assertTrue($referral->rewards->contains($reward));
    }

    public function test_referral_statistics_are_updated(): void
    {
        $user = User::factory()->create();
        $referral = Referral::factory()->create(['referrer_id' => $user->id]);

        // Statistics should be updated when referral is created
        $this->assertDatabaseHas('referral_statistics', [
            'user_id' => $user->id,
            'total_referrals' => 1,
            'pending_referrals' => 1,
        ]);
    }

    public function test_referral_performance_score_calculation(): void
    {
        $referral = Referral::factory()->create(['status' => 'completed']);
        $reward = ReferralReward::factory()->create(['referral_id' => $referral->id]);

        $score = $referral->performance_score;

        $this->assertGreaterThan(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_referral_scope_filters(): void
    {
        $activeReferral = Referral::factory()->create(['status' => 'pending']);
        $completedReferral = Referral::factory()->create(['status' => 'completed']);
        $expiredReferral = Referral::factory()->create(['status' => 'expired']);

        $this->assertCount(1, Referral::active()->get());
        $this->assertCount(1, Referral::completed()->get());
        $this->assertCount(1, Referral::expired()->get());
    }

    public function test_referral_validation_rules(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('referrals.store'), []);

        $response->assertSessionHasErrors(['referred_email']);
    }

    public function test_referral_code_expiration(): void
    {
        $expiredCode = ReferralCode::factory()->create([
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($expiredCode->isValid());
    }

    public function test_referral_reward_application(): void
    {
        $order = Order::factory()->create();
        $reward = ReferralReward::factory()->create(['status' => 'pending']);

        $reward->apply($order->id);

        $this->assertEquals('applied', $reward->fresh()->status);
        $this->assertEquals($order->id, $reward->fresh()->order_id);
        $this->assertNotNull($reward->fresh()->applied_at);
    }
}

