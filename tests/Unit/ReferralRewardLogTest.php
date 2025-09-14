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

    public function test_referral_reward_log_scope_by_action(): void
    {
        $claimedLog = ReferralRewardLog::factory()->create(['action' => 'claimed']);
        $expiredLog = ReferralRewardLog::factory()->create(['action' => 'expired']);

        $claimedLogs = ReferralRewardLog::byAction('claimed')->get();
        $this->assertTrue($claimedLogs->contains($claimedLog));
        $this->assertFalse($claimedLogs->contains($expiredLog));
    }

    public function test_referral_reward_log_scope_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $log1 = ReferralRewardLog::factory()->create(['user_id' => $user1->id]);
        $log2 = ReferralRewardLog::factory()->create(['user_id' => $user2->id]);

        $user1Logs = ReferralRewardLog::byUser($user1->id)->get();
        $this->assertTrue($user1Logs->contains($log1));
        $this->assertFalse($user1Logs->contains($log2));
    }

    public function test_referral_reward_log_scope_by_referral_reward(): void
    {
        $reward1 = ReferralReward::factory()->create();
        $reward2 = ReferralReward::factory()->create();
        
        $log1 = ReferralRewardLog::factory()->create(['referral_reward_id' => $reward1->id]);
        $log2 = ReferralRewardLog::factory()->create(['referral_reward_id' => $reward2->id]);

        $reward1Logs = ReferralRewardLog::byReferralReward($reward1->id)->get();
        $this->assertTrue($reward1Logs->contains($log1));
        $this->assertFalse($reward1Logs->contains($log2));
    }

    public function test_referral_reward_log_scope_today(): void
    {
        $todayLog = ReferralRewardLog::factory()->create(['created_at' => now()]);
        $yesterdayLog = ReferralRewardLog::factory()->create(['created_at' => now()->subDay()]);

        $todayLogs = ReferralRewardLog::today()->get();
        $this->assertTrue($todayLogs->contains($todayLog));
        $this->assertFalse($todayLogs->contains($yesterdayLog));
    }

    public function test_referral_reward_log_scope_this_week(): void
    {
        $thisWeekLog = ReferralRewardLog::factory()->create(['created_at' => now()]);
        $lastWeekLog = ReferralRewardLog::factory()->create(['created_at' => now()->subWeek()]);

        $thisWeekLogs = ReferralRewardLog::thisWeek()->get();
        $this->assertTrue($thisWeekLogs->contains($thisWeekLog));
        $this->assertFalse($thisWeekLogs->contains($lastWeekLog));
    }

    public function test_referral_reward_log_scope_this_month(): void
    {
        $thisMonthLog = ReferralRewardLog::factory()->create(['created_at' => now()]);
        $lastMonthLog = ReferralRewardLog::factory()->create(['created_at' => now()->subMonth()]);

        $thisMonthLogs = ReferralRewardLog::thisMonth()->get();
        $this->assertTrue($thisMonthLogs->contains($thisMonthLog));
        $this->assertFalse($thisMonthLogs->contains($lastMonthLog));
    }

    public function test_referral_reward_log_scope_recent(): void
    {
        $recentLog = ReferralRewardLog::factory()->create(['created_at' => now()]);
        $oldLog = ReferralRewardLog::factory()->create(['created_at' => now()->subDays(10)]);

        $recentLogs = ReferralRewardLog::recent()->get();
        $this->assertTrue($recentLogs->contains($recentLog));
        $this->assertFalse($recentLogs->contains($oldLog));
    }

    public function test_referral_reward_log_get_action_label_method(): void
    {
        $claimedLog = ReferralRewardLog::factory()->create(['action' => 'claimed']);
        $expiredLog = ReferralRewardLog::factory()->create(['action' => 'expired']);
        $cancelledLog = ReferralRewardLog::factory()->create(['action' => 'cancelled']);

        $this->assertEquals('Claimed', $claimedLog->getActionLabel());
        $this->assertEquals('Expired', $expiredLog->getActionLabel());
        $this->assertEquals('Cancelled', $cancelledLog->getActionLabel());
    }

    public function test_referral_reward_log_get_action_color_method(): void
    {
        $claimedLog = ReferralRewardLog::factory()->create(['action' => 'claimed']);
        $expiredLog = ReferralRewardLog::factory()->create(['action' => 'expired']);
        $cancelledLog = ReferralRewardLog::factory()->create(['action' => 'cancelled']);

        $this->assertEquals('success', $claimedLog->getActionColor());
        $this->assertEquals('warning', $expiredLog->getActionColor());
        $this->assertEquals('danger', $cancelledLog->getActionColor());
    }

    public function test_referral_reward_log_has_data_method(): void
    {
        $logWithData = ReferralRewardLog::factory()->create(['data' => ['amount' => 10.00]]);
        $logWithoutData = ReferralRewardLog::factory()->create(['data' => null]);

        $this->assertTrue($logWithData->hasData());
        $this->assertFalse($logWithoutData->hasData());
    }

    public function test_referral_reward_log_get_data_value_method(): void
    {
        $log = ReferralRewardLog::factory()->create(['data' => ['amount' => 10.00, 'currency' => 'EUR']]);

        $this->assertEquals(10.00, $log->getDataValue('amount'));
        $this->assertEquals('EUR', $log->getDataValue('currency'));
        $this->assertNull($log->getDataValue('non_existent'));
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
