<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralRewardLogResource\Pages\ListReferralRewardLogs;
use App\Filament\Resources\ReferralRewardLogResource\Pages\ViewReferralRewardLog;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralRewardLogResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Register the admin Filament panel context before mounting any Livewire components.
        $this->resolveAdminPanel();

        // Authenticate an administrator capable of accessing the referral reward log resource.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_handles_missing_user_agent_gracefully(): void
    {
        // Seed a reward and log entry with a null user agent to replicate the bug this regression targets.
        $reward = ReferralReward::factory()->create();
        $log = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $reward->getKey(),
            'user_agent'         => null,
        ]);

        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$log]);
    }

    public function test_view_page_displays_log_metadata(): void
    {
        // Build a detailed log payload so the infolist renders contextual information for operators.
        $reward = ReferralReward::factory()->create();
        $log = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $reward->getKey(),
            'action'             => ReferralRewardLog::ACTION_REDEEMED,
            'data'               => [
                'amount'      => 15,
                'currency'    => 'EUR',
                'redeemed_at' => now()->toIso8601String(),
            ],
        ]);

        Livewire::test(ViewReferralRewardLog::class, ['record' => $log->getRouteKey()])
            ->assertSee((string) $reward->getKey())
            ->assertSee('redeemed')
            ->assertSee('15');
    }

    public function test_action_filter_limits_logs_by_status(): void
    {
        // Create logs representing multiple actions so the filter can differentiate earned versus expired events.
        $earned = ReferralRewardLog::factory()->create(['action' => ReferralRewardLog::ACTION_EARNED]);
        $expired = ReferralRewardLog::factory()->create(['action' => ReferralRewardLog::ACTION_EXPIRED]);

        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->filterTable('action', ReferralRewardLog::ACTION_EARNED)
            ->assertCanSeeTableRecords([$earned])
            ->assertCanNotSeeTableRecords([$expired]);
    }
}
