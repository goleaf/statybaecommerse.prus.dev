<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralRewardResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Boot the Filament admin panel context so Livewire components resolve the correct configuration.
        $this->resolveAdminPanel();

        // Normalise locales to guarantee translated fields render deterministically in assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Provision an administrator matching the panel guard expectations and authenticate for the test run.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_referral_rewards(): void
    {
        // Seed a referral reward with deterministic metadata so the table row can be asserted precisely.
        $reward = ReferralReward::factory()
            ->forUser($this->admin)
            ->state([
                'title'       => ['en' => 'Launch Bonus', 'lt' => 'Launch Bonus LT'],
                'type'        => 'discount',
                'status'      => 'pending',
                'amount'      => 25.00,
                'applied_at'  => Carbon::now(),
                'expires_at'  => Carbon::now()->addWeek(),
                'is_active'   => true,
                'priority'    => 5,
                'conditions'  => ['orders' => '>=3'],
                'reward_data' => ['category' => 'discount'],
            ])
            ->create();

        // Render the list component and ensure the seeded reward record is visible within the table output.
        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$reward]);
    }

    public function test_table_filters_referral_rewards_by_status_and_type(): void
    {
        // Create a pending discount reward that should remain visible after applying both filters.
        $matchingReward = ReferralReward::factory()
            ->forUser($this->admin)
            ->state([
                'title'  => ['en' => 'Pending Discount', 'lt' => 'Laukiama Nuolaida'],
                'type'   => 'discount',
                'status' => 'pending',
            ])
            ->create();

        // Create an applied credit reward that the filtered dataset should exclude.
        $hiddenReward = ReferralReward::factory()
            ->forUser(User::factory()->create())
            ->state([
                'title'  => ['en' => 'Applied Credit', 'lt' => 'Pritaikytas Kreditas'],
                'type'   => 'credit',
                'status' => 'applied',
            ])
            ->create();

        // Apply both the status and type filters to narrow the table dataset to the desired reward.
        Livewire::test(ListReferralRewards::class)
            ->call('loadTable')
            ->filterTable('status', 'pending')
            ->filterTable('type', 'discount')
            ->assertCanSeeTableRecords([$matchingReward])
            ->assertCanNotSeeTableRecords([$hiddenReward]);
    }
}
