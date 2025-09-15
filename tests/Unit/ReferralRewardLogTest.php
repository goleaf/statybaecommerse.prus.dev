<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReferralRewardLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_reward_log_can_be_created(): void
    {
        $referralReward = ReferralReward::factory()->create();
        $user = User::factory()->create();
        
        $log = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $referralReward->id,
            'user_id' => $user->id,
            'action' => 'claimed',
            'data' => ['amount' => 10.00, 'currency' => 'EUR'],
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);

        $this->assertInstanceOf(ReferralRewardLog::class, $log);
        $this->assertEquals($referralReward->id, $log->referral_reward_id);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('claimed', $log->action);
        $this->assertIsArray($log->data);
        $this->assertEquals(10.00, $log->data['amount']);
        $this->assertEquals('EUR', $log->data['currency']);
        $this->assertEquals('192.168.1.1', $log->ip_address);
        $this->assertNotEmpty($log->user_agent);
    }

    public function test_referral_reward_log_fillable_attributes(): void
    {
        $log = new ReferralRewardLog();
        $fillable = $log->getFillable();

        $expectedFillable = [
            'referral_reward_id',
            'user_id',
            'action',
            'data',
            'ip_address',
            'user_agent',
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_referral_reward_log_casts(): void
    {
        $log = ReferralRewardLog::factory()->create([
            'data' => ['test' => 'data'],
        ]);

        $this->assertIsArray($log->data);
        $this->assertEquals('data', $log->data['test']);
    }

    public function test_referral_reward_log_belongs_to_referral_reward(): void
    {
        $referralReward = ReferralReward::factory()->create();
        $log = ReferralRewardLog::factory()->create(['referral_reward_id' => $referralReward->id]);

        $this->assertInstanceOf(ReferralReward::class, $log->referralReward);
        $this->assertEquals($referralReward->id, $log->referralReward->id);
    }

    public function test_referral_reward_log_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $log = ReferralRewardLog::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($user->id, $log->user->id);
    }

    public function test_referral_reward_log_basic_functionality(): void
    {
        $referralReward = ReferralReward::factory()->create();
        $user = User::factory()->create();
        
        $log = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $referralReward->id,
            'user_id' => $user->id,
            'action' => 'claimed',
            'data' => ['amount' => 10.00, 'currency' => 'EUR'],
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);

        // Test basic model functionality
        $this->assertInstanceOf(ReferralRewardLog::class, $log);
        $this->assertEquals($referralReward->id, $log->referral_reward_id);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('claimed', $log->action);
        $this->assertEquals('192.168.1.1', $log->ip_address);
        $this->assertNotEmpty($log->user_agent);
    }

    public function test_referral_reward_log_data_access(): void
    {
        $log = ReferralRewardLog::factory()->create([
            'data' => [
                'amount' => 10.00,
                'currency' => 'EUR',
                'reward_type' => 'discount',
            ],
        ]);

        $this->assertIsArray($log->data);
        $this->assertEquals(10.00, $log->data['amount']);
        $this->assertEquals('EUR', $log->data['currency']);
        $this->assertEquals('discount', $log->data['reward_type']);
    }

    public function test_referral_reward_log_timestamps(): void
    {
        $log = ReferralRewardLog::factory()->create();

        $this->assertInstanceOf(\Carbon\Carbon::class, $log->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $log->updated_at);
        $this->assertNotNull($log->created_at);
        $this->assertNotNull($log->updated_at);
    }

    public function test_referral_reward_log_table_name(): void
    {
        $log = new ReferralRewardLog();
        $this->assertEquals('referral_reward_logs', $log->getTable());
    }

    public function test_referral_reward_log_factory(): void
    {
        $log = ReferralRewardLog::factory()->create();

        $this->assertInstanceOf(ReferralRewardLog::class, $log);
        $this->assertNotEmpty($log->referral_reward_id);
        $this->assertNotEmpty($log->user_id);
        $this->assertNotEmpty($log->action);
        $this->assertIsArray($log->data);
        $this->assertNotEmpty($log->ip_address);
        $this->assertNotEmpty($log->user_agent);
    }
}
