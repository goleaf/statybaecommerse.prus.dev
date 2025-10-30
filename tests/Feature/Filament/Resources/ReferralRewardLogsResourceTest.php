<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralRewardLogs\Pages\EditReferralRewardLog;
use App\Filament\Resources\ReferralRewardLogs\Pages\ListReferralRewardLogs;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralRewardLogsResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament bootstraps the admin panel so Livewire pages resolve correctly.
        $this->resolveAdminPanel();

        // Sign in as the canonical admin user to satisfy resource authorization policies.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_renders_reward_logs(): void
    {
        // Seed a referral reward and user so the log row has visible relationships.
        $reward = ReferralReward::factory()->create([
            'title' => ['en' => 'Coverage Reward'],
        ]);
        $recipient = User::factory()->create([
            'name' => 'Reward Recipient',
        ]);

        $log = ReferralRewardLog::factory()
            ->for($reward, 'referralReward')
            ->for($recipient, 'user')
            ->create([
                'action'     => ReferralRewardLog::ACTION_EARNED,
                'ip_address' => '198.51.100.40',
            ]);

        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$log]);
    }

    public function test_edit_form_allows_action_transitions(): void
    {
        // Create a log entry to exercise the edit form validation and persistence logic.
        $reward = ReferralReward::factory()->create([
            'title' => ['en' => 'Editable Reward'],
        ]);
        $recipient = User::factory()->create([
            'name' => 'Editable Recipient',
        ]);

        $log = ReferralRewardLog::factory()
            ->for($reward, 'referralReward')
            ->for($recipient, 'user')
            ->create([
                'action'     => ReferralRewardLog::ACTION_EARNED,
                'ip_address' => '203.0.113.40',
                'user_agent' => 'Agent/1.0',
            ]);

        Livewire::test(EditReferralRewardLog::class, ['record' => $log->getKey()])
            ->fillForm([
                // Promote the reward lifecycle to redeemed to ensure select options persist correctly.
                'action'     => ReferralRewardLog::ACTION_REDEEMED,
                'ip_address' => '203.0.113.41',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_reward_logs', [
            'id'         => $log->getKey(),
            'action'     => ReferralRewardLog::ACTION_REDEEMED,
            'ip_address' => '203.0.113.41',
        ]);
    }

    public function test_bulk_delete_removes_selected_logs(): void
    {
        // Generate two logs so the bulk delete action can target a subset of rows.
        $reward = ReferralReward::factory()->create([
            'title' => ['en' => 'Bulk Reward'],
        ]);
        $recipient = User::factory()->create([
            'name' => 'Bulk Recipient',
        ]);

        $logToDelete = ReferralRewardLog::factory()
            ->for($reward, 'referralReward')
            ->for($recipient, 'user')
            ->create([
                'action' => ReferralRewardLog::ACTION_CANCELLED,
            ]);

        $logToKeep = ReferralRewardLog::factory()
            ->for($reward, 'referralReward')
            ->for($recipient, 'user')
            ->create([
                'action' => ReferralRewardLog::ACTION_EARNED,
            ]);

        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->callTableBulkAction('delete', [$logToDelete->getKey()])
            ->assertHasNoTableActionErrors();

        $this->assertModelMissing($logToDelete);
        $this->assertModelExists($logToKeep);
    }
}
