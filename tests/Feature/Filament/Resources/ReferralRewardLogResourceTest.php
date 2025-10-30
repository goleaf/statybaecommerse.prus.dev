<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralRewardLogResource\Pages\ListReferralRewardLogs;
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

        // Ensure Filament panel providers boot before interacting with the Livewire list page.
        $this->resolveAdminPanel();

        // Authenticate as the default administrator so resource policies allow access during assertions.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_renders_reward_logs(): void
    {
        // Prepare a reward owner and reward record so the log relationship has stable fixture data.
        $rewardOwner = User::factory()->create([
            'email' => 'reward.owner@example.com',
        ]);

        $referralReward = ReferralReward::factory()->for($rewardOwner)->create([
            'amount'        => 42.50,
            'currency_code' => 'EUR',
        ]);

        // Persist a reward log entry with deterministic payload values for confident table assertions.
        $rewardLog = ReferralRewardLog::factory()
            ->for($referralReward, 'referralReward')
            ->for(User::factory()->create(['email' => 'reward.recipient@example.com']))
            ->create([
                'action'     => ReferralRewardLog::ACTION_EARNED,
                'data'       => [
                    'amount'      => 42.50,
                    'currency'    => 'EUR',
                    'reward_type' => 'discount',
                ],
                'ip_address' => '198.51.100.25',
                'user_agent' => 'FilamentCoverage/2.0',
            ]);

        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$rewardLog]);
    }
}
