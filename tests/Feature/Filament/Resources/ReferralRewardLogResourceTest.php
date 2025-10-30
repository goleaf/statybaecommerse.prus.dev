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
 * Livewire regression tests for the referral reward log administration resource.
 */
final class ReferralRewardLogResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialise the Filament admin context so resource components boot without manual panel wiring.
        $this->resolveAdminPanel();

        // Normalise locale-sensitive factories to English to keep assertions consistent across environments.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Log in as the canonical admin account to satisfy resource authorization checks.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_surfaces_reward_logs(): void
    {
        // Arrange: create a referral reward log that should appear in the listing for verification.
        $reward = $this->createReward([
            'title'       => ['en' => 'Logged reward'],
            'reward_data' => ['category' => 'credit'],
        ]);
        $log = ReferralRewardLog::factory()->for($reward)->create([
            'user_id' => $this->admin->getKey(),
            'action'  => ReferralRewardLog::ACTION_EARNED,
        ]);

        // Act & Assert: hydrate the table dataset and confirm the created log record is visible.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$log])
            ->assertSee($log->referralReward->getKey());
    }

    public function test_filters_reward_logs_by_user_and_action(): void
    {
        // Arrange: seed an "earned" log tied to the admin and a secondary record for noise.
        $matchingReward = $this->createReward([
            'title'       => ['en' => 'Earned reward'],
            'reward_data' => ['category' => 'discount'],
        ]);
        $matching = ReferralRewardLog::factory()->for($matchingReward)->create([
            'user_id' => $this->admin->getKey(),
            'action'  => ReferralRewardLog::ACTION_EARNED,
        ]);
        $otherReward = $this->createReward([
            'title'       => ['en' => 'Redeemed reward'],
            'reward_data' => ['category' => 'gift'],
        ]);
        $other = ReferralRewardLog::factory()->for($otherReward)->redeemed()->create();

        // Act: apply the user and action filters to the Livewire table component.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->filterTable('user_id', $this->admin->getKey())
            ->filterTable('action', ReferralRewardLog::ACTION_EARNED)
            // Assert: only the matching log remains visible after filtering.
            ->assertCanSeeTableRecords([$matching])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_filters_reward_logs_by_parent_reward(): void
    {
        // Arrange: create logs tied to different rewards to exercise the referral reward filter.
        $targetReward = $this->createReward([
            'title'       => ['en' => 'Target reward'],
            'reward_data' => ['category' => 'points'],
        ]);
        $targetLog = ReferralRewardLog::factory()->for($targetReward)->create([
            'user_id' => $this->admin->getKey(),
        ]);
        $otherReward = $this->createReward([
            'title'       => ['en' => 'Other reward'],
            'reward_data' => ['category' => 'discount'],
        ]);
        $otherLog = ReferralRewardLog::factory()->for($otherReward)->create();

        // Act: filter the listing by the referral reward identifier captured on the primary log.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->filterTable('referral_reward_id', $targetLog->referral_reward_id)
            // Assert: only logs matching the selected reward remain visible after filtering.
            ->assertCanSeeTableRecords([$targetLog])
            ->assertCanNotSeeTableRecords([$otherLog]);
    }

    /**
     * Create a deterministic referral reward for log scenarios without invoking the flaky factory state.
     */
    private function createReward(array $overrides = []): ReferralReward
    {
        $base = [
            'referral_id'    => null,
            'user_id'        => $this->admin->getKey(),
            'order_id'       => null,
            'type'           => 'discount',
            'title'          => ['en' => 'Seed reward'],
            'description'    => ['en' => 'Seed description'],
            'amount'         => 5.00,
            'currency_code'  => 'EUR',
            'status'         => 'pending',
            'applied_at'     => null,
            'expires_at'     => null,
            'is_active'      => true,
            'priority'       => 1,
            'conditions'     => [],
            'reward_data'    => ['category' => 'discount'],
            'metadata'       => [],
        ];

        return ReferralReward::query()->create(array_replace_recursive($base, $overrides));
    }
}
