<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Dedicated Filament v4 coverage for the referral reward management resource.
 */
final class ReferralRewardResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Boot the Filament admin panel so resource routes and Livewire components resolve correctly.
        $this->resolveAdminPanel();

        // Lock the locale to English to keep translated factory payloads deterministic in assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate as an admin to bypass authorization gates inside the resource policies.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_referral_rewards(): void
    {
        // Arrange: seed a reward tied to the admin so the listing has a visible record.
        $reward = $this->createReward([
            'title' => ['en' => 'Launch bonus'],
        ]);

        // Act & Assert: hydrate the table and confirm the seeded reward appears in the grid.
        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$reward])
            ->assertSee('Launch bonus');
    }

    public function test_filters_rewards_by_user_and_status(): void
    {
        // Arrange: create a matching applied reward for the admin and a control reward for noise.
        $matching = $this->createReward([
            'title'       => ['en' => 'Applied incentive'],
            'status'      => 'applied',
            'applied_at'  => now(),
            'type'        => 'credit',
            'reward_data' => ['category' => 'credit'],
        ]);
        $otherUser = User::factory()->create();
        $other = $this->createReward([
            'title'    => ['en' => 'Pending fallback'],
            'status'   => 'pending',
            'user_id'  => $otherUser->getKey(),
            'type'     => 'discount',
            'metadata' => [],
        ]);

        // Act: filter the table by the admin user and applied status.
        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->filterTable('user_id', $this->admin->getKey())
            ->filterTable('status', 'applied')
            // Assert: the applied reward remains visible while the unrelated record is hidden.
            ->assertCanSeeTableRecords([$matching])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_table_apply_action_marks_reward_as_applied(): void
    {
        // Arrange: seed a pending reward that can transition to the applied state via the table action.
        $reward = $this->createReward([
            'title'  => ['en' => 'Actionable bonus'],
            'status' => 'pending',
        ]);

        // Act: trigger the "apply" table action against the pending reward.
        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->callTableAction('apply', $reward)
            ->assertHasNoTableActionErrors();

        // Assert: the reward transitions to the applied state with an application timestamp.
        $reward->refresh();
        self::assertSame('applied', $reward->status);
        self::assertNotNull($reward->applied_at);
}

    /**
     * Provide a compact helper for creating referral rewards without invoking factory randomness.
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
            'amount'         => 10.00,
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
