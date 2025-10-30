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
 * Smoke coverage for the Filament v4 ReferralRewardLog resource pages.
 */
final class ReferralRewardLogResourceV4Test extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament boots the admin panel context before any Livewire component mounts.
        $this->resolveAdminPanel();

        // Normalise locale-dependent factories so seeded strings remain predictable across assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate as an administrator so every Filament resource interaction passes authorization checks.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_referral_reward_logs(): void
    {
        // Seed a referral reward record that the subsequent log entry can reference without lazy factory lookups.
        $reward = $this->createReferralReward([
            'title'       => ['en' => 'Signup Bonus', 'lt' => 'Registracijos premija'],
            'description' => ['en' => 'Signup description', 'lt' => 'Registracijos aprašas'],
        ]);

        // Persist a deterministic log entry to verify the table renders seeded data correctly.
        $log = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $reward->getKey(),
            'user_id'            => $this->admin->getKey(),
            'action'             => ReferralRewardLog::ACTION_EARNED,
            'ip_address'         => '203.0.113.10',
            'user_agent'         => 'Mozilla/5.0 Filament Test',
        ]);

        // Hydrate the table data before asserting the record is visible on the listing page.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$log]);
    }

    public function test_filters_limit_logs_to_selected_reward_and_user(): void
    {
        // Create two distinct rewards to exercise the referral reward filter logic accurately.
        $targetReward = $this->createReferralReward([
            'title'       => ['en' => 'Target Reward', 'lt' => 'Taikinio premija'],
            'description' => ['en' => 'Target description', 'lt' => 'Taikinio aprašas'],
        ]);
        $otherReward = $this->createReferralReward([
            'title'       => ['en' => 'Other Reward', 'lt' => 'Kita premija'],
            'description' => ['en' => 'Other description', 'lt' => 'Kitas aprašas'],
            'reward_data' => ['category' => 'credit'],
        ]);

        // Persist a second account to ensure the user filter differentiates between multiple authors.
        $secondaryUser = User::factory()->create();

        // Insert the record expected to survive both filters.
        $matchingLog = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $targetReward->getKey(),
            'user_id'            => $this->admin->getKey(),
            'action'             => ReferralRewardLog::ACTION_REDEEMED,
        ]);

        // Insert a control record that should be excluded once the filters are applied.
        $otherLog = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $otherReward->getKey(),
            'user_id'            => $secondaryUser->getKey(),
        ]);

        // Apply both table filters and confirm only the matching record remains visible.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->filterTable('referral_reward_id', (string) $targetReward->getKey())
            ->filterTable('user_id', (string) $this->admin->getKey())
            ->assertCanSeeTableRecords([$matchingLog])
            ->assertCanNotSeeTableRecords([$otherLog]);
    }

    /**
     * Create a referral reward record without relying on the factory helper that expects randomised JSON payloads.
     */
    private function createReferralReward(array $overrides = []): ReferralReward
    {
        // Merge the deterministic defaults with any caller overrides so tests stay expressive and resilient.
        $defaults = [
            'user_id'      => $this->admin->getKey(),
            'type'         => 'referrer_bonus',
            'amount'       => 25.50,
            'currency_code'=> 'EUR',
            'status'       => 'pending',
            'title'        => ['en' => 'Default Reward', 'lt' => 'Numatytoji premija'],
            'description'  => ['en' => 'Default description', 'lt' => 'Numatytasis aprašas'],
            'is_active'    => true,
            'priority'     => 0,
            'conditions'   => [],
            'reward_data'  => ['category' => 'discount'],
            'metadata'     => [],
        ];

        return ReferralReward::query()->create(array_merge($defaults, $overrides));
    }
}
