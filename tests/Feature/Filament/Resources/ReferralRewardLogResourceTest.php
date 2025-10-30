<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralRewardLogResource;
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

    private ReferralReward $reward;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialise the Filament admin panel to ensure panel-specific service providers are loaded.
        $this->resolveAdminPanel();

        // Stabilise localisation output for consistent assertions when rendering badge and date columns.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Prepare an administrator user who can access the referral reward logs without policy friction.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);

        // Seed a referral reward to attach subsequent log entries against.
        $this->reward = ReferralReward::query()->create([
            'referral_id' => null,
            'user_id'     => $this->admin->id,
            'order_id'    => null,
            'type'        => 'credit',
            'title'       => ['en' => 'Referral Bonus', 'lt' => 'Referral Bonus LT'],
            'description' => ['en' => 'Bonus issued for inviting friends.', 'lt' => 'Bonus issued for inviting friends.'],
            'amount'      => 15.00,
            'currency_code' => 'EUR',
            'status'      => 'pending',
            'is_active'   => true,
            'priority'    => 1,
            'conditions'  => [],
            'reward_data' => ['category' => 'credit'],
            'metadata'    => [],
        ]);
    }

    public function test_index_page_is_accessible(): void
    {
        // Confirm routing works for the Filament list page before drilling into Livewire assertions.
        $this
            ->get(ReferralRewardLogResource::getUrl('index'))
            ->assertOk();
    }

    public function test_list_page_displays_reward_log_record(): void
    {
        // Attach a deterministic log entry to assert badge rendering for the "earned" action.
        $log = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $this->reward->id,
            'user_id'            => $this->admin->id,
            'action'             => ReferralRewardLog::ACTION_EARNED,
            'ip_address'         => '203.0.113.77',
            'user_agent'         => 'ReferralLogTestAgent/1.0',
        ]);

        // Load the Livewire table and assert the seeded log appears alongside its action badge.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$log])
            ->assertSee('earned')
            ->assertSee('203.0.113.77');
    }
}
