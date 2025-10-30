<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralRewardLogs\Pages\ListReferralRewardLogs;
use App\Models\ReferralRewardLog;
use App\Models\User;
use App\Models\ReferralReward;
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

        // Resolve the Filament admin panel context so Livewire components boot with correct configuration.
        $this->resolveAdminPanel();

        // Normalise locale to keep translated attributes deterministic during assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Create and authenticate a privileged administrator to satisfy Filament authorization checks.
        $this->admin = User::factory()->create([
            'email'    => 'reward-logs-admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_referral_reward_log_details(): void
    {
        // Seed a reward with a predictable title so the table assertion remains explicit.
        $reward = $this->createReferralReward([
            'title' => [
                'en' => 'Loyalty Bonus',
                'lt' => 'Loyalty Bonus',
            ],
        ]);

        // Attach a log entry to the reward ensuring the related user name is unique for visibility assertions.
        $log = ReferralRewardLog::factory()
            ->for($reward, 'referralReward')
            ->for(User::factory()->create(['name' => 'Visible Reward User']), 'user')
            ->create([
                'action'     => ReferralRewardLog::ACTION_EARNED,
                'user_agent' => 'Mozilla/5.0 Filament Test Suite',
            ]);

        // Assert the admin listing shows the expected reward and user details once the table is hydrated.
        Livewire::actingAs($this->admin)
            ->test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$log])
            ->assertSee('Loyalty Bonus')
            ->assertSee('Visible Reward User');
    }

    public function test_bulk_delete_action_removes_selected_logs(): void
    {
        // Reuse a single reward to avoid triggering nested factory calls during log creation.
        $reward = $this->createReferralReward();

        // Generate a pair of logs so we can target one for deletion and keep the other as a control record.
        $logs = ReferralRewardLog::factory()
            ->count(2)
            ->for($reward, 'referralReward')
            ->create();

        // Trigger the bulk delete action and ensure no validation errors are returned by the Livewire component.
        Livewire::actingAs($this->admin)
            ->test(ListReferralRewardLogs::class)
            ->callTableBulkAction('delete', [$logs[0]])
            ->assertHasNoTableBulkActionErrors();

        // Confirm the chosen log has been removed while the remaining log is still present.
        $this->assertDatabaseMissing('referral_reward_logs', ['id' => $logs[0]->id]);
        $this->assertDatabaseHas('referral_reward_logs', ['id' => $logs[1]->id]);
    }

    public function test_search_finds_logs_by_user_name(): void
    {
        // Reuse a reward instance to keep the test deterministic and avoid randomised factory behaviour.
        $reward = $this->createReferralReward();

        // Create two logs with contrasting user names to verify the global search behaves as expected.
        $matchingLog = ReferralRewardLog::factory()
            ->for($reward, 'referralReward')
            ->for(User::factory()->create(['name' => 'Searchable Reward User']), 'user')
            ->create();
        $hiddenLog = ReferralRewardLog::factory()
            ->for($reward, 'referralReward')
            ->for(User::factory()->create(['name' => 'Background User']), 'user')
            ->create();

        // Apply the table search and assert only the matching record remains visible after hydration.
        Livewire::actingAs($this->admin)
            ->test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->searchTable('Searchable Reward User')
            ->assertCanSeeTableRecords([$matchingLog])
            ->assertCanNotSeeTableRecords([$hiddenLog]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createReferralReward(array $overrides = []): ReferralReward
    {
        // Ensure a concrete reward owner exists before persisting the reward payload.
        $user = $overrides['user'] ?? User::factory()->create(['name' => 'Reward Owner']);
        unset($overrides['user']);

        // Build a deterministic reward payload mirroring the required schema columns.
        $baseAttributes = [
            'referral_id'   => null,
            'user_id'       => $user->id,
            'order_id'      => null,
            'type'          => 'discount',
            'title'         => [
                'en' => 'Base Reward Title',
                'lt' => 'Base Reward Title',
            ],
            'description'   => [
                'en' => 'Base reward description',
                'lt' => 'Base reward description',
            ],
            'amount'        => 25.00,
            'currency_code' => 'EUR',
            'status'        => 'pending',
            'applied_at'    => null,
            'expires_at'    => null,
            'metadata'      => [],
            'is_active'     => true,
            'priority'      => 0,
            'conditions'    => [],
            'reward_data'   => ['category' => 'discount'],
            'created_at'    => now(),
            'updated_at'    => now(),
        ];

        return ReferralReward::query()->create(array_merge($baseAttributes, $overrides));
    }
}
