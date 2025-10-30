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

/**
 * Feature coverage for the Filament v4 referral reward log resource table interactions.
 */
final class ReferralRewardLogResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ReferralReward $referralReward;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel to ensure the v4 panel context is active for Livewire tests.
        $this->resolveAdminPanel();

        // Lock the application locale to English so seeded translations remain deterministic.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate as an administrator to bypass authorization policies during table interactions.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Create a referral reward parent record that subsequent log entries can reference reliably.
        $this->referralReward = ReferralReward::factory()->create([
            'title' => ['en' => 'Coverage Reward', 'lt' => 'Coverage Reward'],
        ]);

        // Ensure every request executes with the administrator guard context.
        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_reward_logs(): void
    {
        // Persist a reward log that should be visible in the admin listing table.
        $log = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $this->referralReward->getKey(),
            'user_id'            => User::factory()->create()->getKey(),
            'action'             => ReferralRewardLog::ACTION_EARNED,
            'data'               => ['amount' => 15.0, 'currency' => 'EUR'],
            'user_agent'         => null,
        ]);

        // Hydrate the table data and confirm the seeded log appears for the administrator.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$log])
            ->assertSee('earned');
    }

    public function test_table_action_filter_limits_results_to_requested_action(): void
    {
        // Seed logs for different actions so the filter can differentiate between earned and redeemed entries.
        $earnedLog = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $this->referralReward->getKey(),
            'action'             => ReferralRewardLog::ACTION_EARNED,
        ]);
        $redeemedLog = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $this->referralReward->getKey(),
            'action'             => ReferralRewardLog::ACTION_REDEEMED,
        ]);

        // Apply the action filter and ensure only earned logs remain visible within the table dataset.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->filterTable('action', ReferralRewardLog::ACTION_EARNED)
            ->assertCanSeeTableRecords([$earnedLog])
            ->assertCanNotSeeTableRecords([$redeemedLog]);
    }

    public function test_table_bulk_delete_removes_selected_logs(): void
    {
        // Seed a handful of logs that should be deleted through the bulk table action.
        $logs = ReferralRewardLog::factory()->count(2)->create([
            'referral_reward_id' => $this->referralReward->getKey(),
        ]);

        // Invoke the bulk delete action and assert the records vanish from the database.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->callTableBulkAction('delete', $logs)
            ->assertHasNoTableBulkActionErrors();

        foreach ($logs as $log) {
            // Confirm each log has been removed to prove the bulk delete succeeded end-to-end.
            $this->assertDatabaseMissing('referral_reward_logs', ['id' => $log->id]);
        }
    }
}
