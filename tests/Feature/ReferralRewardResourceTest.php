<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReferralRewardResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_referral_reward(): void
    {
        $user = User::factory()->create();
        
        $rewardData = [
            'user_id' => $user->id,
            'type' => 'discount',
            'amount' => 10.00,
            'status' => 'pending',
            'description' => 'Referral discount reward',
            'is_active' => true,
        ];

        $reward = ReferralReward::create($rewardData);

        $this->assertDatabaseHas('referral_rewards', [
            'user_id' => $user->id,
            'type' => 'discount',
            'amount' => 10.00,
            'status' => 'pending',
        ]);

        $this->assertEquals($user->id, $reward->user_id);
        $this->assertEquals('discount', $reward->type);
        $this->assertEquals(10.00, $reward->amount);
        $this->assertEquals('pending', $reward->status);
    }

    public function test_can_update_referral_reward(): void
    {
        $reward = ReferralReward::factory()->create();

        $reward->update([
            'status' => 'approved',
            'amount' => 15.00,
        ]);

        $this->assertEquals('approved', $reward->status);
        $this->assertEquals(15.00, $reward->amount);
    }

    public function test_can_filter_referral_rewards_by_type(): void
    {
        ReferralReward::factory()->create(['type' => 'discount']);
        ReferralReward::factory()->create(['type' => 'credit']);

        $discountRewards = ReferralReward::where('type', 'discount')->get();
        $creditRewards = ReferralReward::where('type', 'credit')->get();

        $this->assertCount(1, $discountRewards);
        $this->assertCount(1, $creditRewards);
        $this->assertEquals('discount', $discountRewards->first()->type);
        $this->assertEquals('credit', $creditRewards->first()->type);
    }

    public function test_can_filter_referral_rewards_by_status(): void
    {
        ReferralReward::factory()->create(['status' => 'pending']);
        ReferralReward::factory()->create(['status' => 'approved']);

        $pendingRewards = ReferralReward::where('status', 'pending')->get();
        $approvedRewards = ReferralReward::where('status', 'approved')->get();

        $this->assertCount(1, $pendingRewards);
        $this->assertCount(1, $approvedRewards);
        $this->assertEquals('pending', $pendingRewards->first()->status);
        $this->assertEquals('approved', $approvedRewards->first()->status);
    }

    public function test_can_filter_referral_rewards_by_active_status(): void
    {
        ReferralReward::factory()->create(['is_active' => true]);
        ReferralReward::factory()->create(['is_active' => false]);

        $activeRewards = ReferralReward::where('is_active', true)->get();
        $inactiveRewards = ReferralReward::where('is_active', false)->get();

        $this->assertCount(1, $activeRewards);
        $this->assertCount(1, $inactiveRewards);
        $this->assertTrue($activeRewards->first()->is_active);
        $this->assertFalse($inactiveRewards->first()->is_active);
    }

    public function test_can_calculate_total_reward_amount(): void
    {
        $user = User::factory()->create();
        
        ReferralReward::factory()->create([
            'user_id' => $user->id,
            'amount' => 10.00,
            'status' => 'paid',
        ]);
        
        ReferralReward::factory()->create([
            'user_id' => $user->id,
            'amount' => 15.00,
            'status' => 'paid',
        ]);

        $totalAmount = ReferralReward::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');

        $this->assertEquals(25.00, $totalAmount);
    }

    public function test_can_get_referral_reward_with_user_relationship(): void
    {
        $user = User::factory()->create();
        
        $reward = ReferralReward::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $reward->user);
        $this->assertEquals($user->id, $reward->user->id);
    }

    public function test_can_soft_delete_referral_reward(): void
    {
        $reward = ReferralReward::factory()->create();

        $reward->delete();

        $this->assertSoftDeleted('referral_rewards', [
            'id' => $reward->id,
        ]);
    }
}
