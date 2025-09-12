<?php declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Referral;
use App\Models\User;
use App\Models\ReferralReward;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReferralControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_view_referrals_index(): void
    {
        $referrals = Referral::factory()->count(3)->create(['referrer_id' => $this->user->id]);

        $response = $this->get(route('referrals.index'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.referrals.index');
        $response->assertViewHas('referrals');
        $response->assertViewHas('totalReferrals');
        $response->assertViewHas('completedReferrals');
        $response->assertViewHas('totalRewards');
        $response->assertViewHas('pendingRewards');
    }

    public function test_can_view_create_referral_form(): void
    {
        $response = $this->get(route('referrals.create'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.referrals.create');
    }

    public function test_can_create_referral(): void
    {
        $referredUser = User::factory()->create();

        $response = $this->post(route('referrals.store'), [
            'referred_email' => $referredUser->email,
            'title' => 'Test Referral',
            'description' => 'Test Description',
        ]);

        $response->assertRedirect(route('referrals.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $this->user->id,
            'referred_id' => $referredUser->id,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_refer_self(): void
    {
        $response = $this->post(route('referrals.store'), [
            'referred_email' => $this->user->email,
            'title' => 'Test Referral',
            'description' => 'Test Description',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', __('referrals.cannot_refer_yourself'));
    }

    public function test_cannot_refer_already_referred_user(): void
    {
        $referredUser = User::factory()->create();
        
        // Create existing referral
        Referral::factory()->create([
            'referred_id' => $referredUser->id,
        ]);

        $response = $this->post(route('referrals.store'), [
            'referred_email' => $referredUser->email,
            'title' => 'Test Referral',
            'description' => 'Test Description',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', __('referrals.user_already_referred'));
    }

    public function test_cannot_refer_when_limit_reached(): void
    {
        // Create 100 referrals to reach limit
        for ($i = 0; $i < 100; $i++) {
            $referredUser = User::factory()->create();
            Referral::factory()->create([
                'referrer_id' => $this->user->id,
                'referred_id' => $referredUser->id,
                'status' => 'pending',
            ]);
        }

        $newReferredUser = User::factory()->create();

        $response = $this->post(route('referrals.store'), [
            'referred_email' => $newReferredUser->email,
            'title' => 'Test Referral',
            'description' => 'Test Description',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', __('referrals.referral_limit_reached'));
    }

    public function test_validation_requires_referred_email(): void
    {
        $response = $this->post(route('referrals.store'), [
            'title' => 'Test Referral',
            'description' => 'Test Description',
        ]);

        $response->assertSessionHasErrors(['referred_email']);
    }

    public function test_validation_requires_valid_email(): void
    {
        $response = $this->post(route('referrals.store'), [
            'referred_email' => 'invalid-email',
            'title' => 'Test Referral',
            'description' => 'Test Description',
        ]);

        $response->assertSessionHasErrors(['referred_email']);
    }

    public function test_validation_requires_existing_user(): void
    {
        $response = $this->post(route('referrals.store'), [
            'referred_email' => 'nonexistent@example.com',
            'title' => 'Test Referral',
            'description' => 'Test Description',
        ]);

        $response->assertSessionHasErrors(['referred_email']);
    }

    public function test_can_generate_referral_code(): void
    {
        $response = $this->post(route('referrals.generate_code'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => __('referrals.code_generated'),
        ]);

        $this->assertNotNull($this->user->fresh()->referral_code);
    }

    public function test_cannot_generate_code_when_already_exists(): void
    {
        $this->user->update(['referral_code' => 'EXISTING']);

        $response = $this->post(route('referrals.generate_code'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => __('referrals.code_already_exists'),
            'code' => 'EXISTING',
        ]);
    }

    public function test_can_apply_referral_code(): void
    {
        $referrer = User::factory()->create();
        $referral = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referral_code' => 'TEST123',
            'status' => 'pending',
        ]);

        $response = $this->post(route('referrals.apply_code'), [
            'code' => 'TEST123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => __('referrals.code_applied'),
        ]);

        $this->assertDatabaseHas('referrals', [
            'id' => $referral->id,
            'referred_id' => $this->user->id,
        ]);
    }

    public function test_cannot_apply_invalid_code(): void
    {
        $response = $this->post(route('referrals.apply_code'), [
            'code' => 'INVALID',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => __('referrals.invalid_code'),
        ]);
    }

    public function test_cannot_apply_own_code(): void
    {
        $referral = Referral::factory()->create([
            'referrer_id' => $this->user->id,
            'referral_code' => 'OWNCODE',
            'status' => 'pending',
        ]);

        $response = $this->post(route('referrals.apply_code'), [
            'code' => 'OWNCODE',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => __('referrals.cannot_use_own_code'),
        ]);
    }

    public function test_cannot_apply_code_when_already_referred(): void
    {
        $referrer = User::factory()->create();
        $referral = Referral::factory()->create([
            'referrer_id' => $referrer->id,
            'referral_code' => 'TEST123',
            'status' => 'pending',
        ]);

        // Create existing referral for this user
        Referral::factory()->create([
            'referred_id' => $this->user->id,
        ]);

        $response = $this->post(route('referrals.apply_code'), [
            'code' => 'TEST123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => __('referrals.user_already_referred'),
        ]);
    }

    public function test_can_view_share_code_page(): void
    {
        $this->user->update(['referral_code' => 'SHARE123']);

        $response = $this->get(route('referrals.share'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.referrals.share');
        $response->assertViewHas('user');
        $response->assertViewHas('shareText');
    }

    public function test_redirects_to_create_when_no_code(): void
    {
        $response = $this->get(route('referrals.share'));

        $response->assertRedirect(route('referrals.create'));
        $response->assertSessionHas('info', __('referrals.no_active_code'));
    }

    public function test_can_view_statistics(): void
    {
        $referrals = Referral::factory()->count(5)->create(['referrer_id' => $this->user->id]);
        $rewards = ReferralReward::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('referrals.statistics'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.referrals.statistics');
        $response->assertViewHas('totalReferrals');
        $response->assertViewHas('completedReferrals');
        $response->assertViewHas('pendingReferrals');
        $response->assertViewHas('expiredReferrals');
        $response->assertViewHas('totalRewards');
        $response->assertViewHas('pendingRewards');
        $response->assertViewHas('appliedRewards');
        $response->assertViewHas('conversionRate');
        $response->assertViewHas('monthlyStats');
    }

    public function test_can_view_rewards(): void
    {
        $rewards = ReferralReward::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('referrals.rewards'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.referrals.rewards');
        $response->assertViewHas('rewards');
        $response->assertViewHas('totalRewards');
        $response->assertViewHas('pendingRewards');
        $response->assertViewHas('appliedRewards');
    }

    public function test_can_view_public_referral_page(): void
    {
        $referral = Referral::factory()->create(['referral_code' => 'PUBLIC123']);

        $response = $this->get(route('referrals.apply', 'PUBLIC123'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.referrals.show');
        $response->assertViewHas('referral');
    }

    public function test_public_referral_page_returns_404_for_invalid_code(): void
    {
        $response = $this->get(route('referrals.apply', 'INVALID'));

        $response->assertStatus(404);
    }

    public function test_referral_creation_includes_tracking_data(): void
    {
        $referredUser = User::factory()->create();

        $response = $this->post(route('referrals.store'), [
            'referred_email' => $referredUser->email,
            'title' => 'Test Referral',
            'description' => 'Test Description',
        ], [
            'HTTP_USER_AGENT' => 'Test Browser',
        ]);

        $response->assertRedirect(route('referrals.index'));

        $referral = Referral::where('referrer_id', $this->user->id)
            ->where('referred_id', $referredUser->id)
            ->first();

        $this->assertEquals('website', $referral->source);
        $this->assertNotNull($referral->ip_address);
        $this->assertEquals('Test Browser', $referral->user_agent);
    }

    public function test_referral_creation_handles_translations(): void
    {
        $referredUser = User::factory()->create();

        $response = $this->post(route('referrals.store'), [
            'referred_email' => $referredUser->email,
            'title' => 'Test Referral',
            'description' => 'Test Description',
        ]);

        $response->assertRedirect(route('referrals.index'));

        $referral = Referral::where('referrer_id', $this->user->id)
            ->where('referred_id', $referredUser->id)
            ->first();

        $this->assertEquals('Test Referral', $referral->getTranslation('title', 'en'));
        $this->assertEquals('Test Description', $referral->getTranslation('description', 'en'));
    }

    public function test_referral_creation_handles_database_transaction_rollback(): void
    {
        $referredUser = User::factory()->create();

        // Mock database error
        \DB::shouldReceive('beginTransaction')->once();
        \DB::shouldReceive('rollBack')->once();

        $response = $this->post(route('referrals.store'), [
            'referred_email' => $referredUser->email,
            'title' => 'Test Referral',
            'description' => 'Test Description',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', __('referrals.referral_creation_failed'));
    }
}


