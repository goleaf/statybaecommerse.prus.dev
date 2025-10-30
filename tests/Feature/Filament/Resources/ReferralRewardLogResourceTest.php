<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralRewardLogResource\Pages\ListReferralRewardLogs;
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

        // Initialise the Filament admin panel so resource components adopt the panel configuration.
        $this->resolveAdminPanel();

        // Lock the locale to English ensuring deterministic table rendering for assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Create and authenticate a canonical administrator for exercising the protected resource pages.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_referral_reward_logs(): void
    {
        // Persist a reward log so the Livewire table has concrete data to present during the assertion.
        $log = ReferralRewardLog::factory()->state([
            'action'     => ReferralRewardLog::ACTION_EARNED,
            'ip_address' => '192.0.2.1',
            'user_agent' => 'Mozilla/5.0 (Test Agent)',
        ])->create();

        // Ensure the Filament list component renders the freshly created log entry.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$log]);
    }

    public function test_table_filters_logs_by_action(): void
    {
        // Create logs across multiple actions to validate the select filter narrows the dataset correctly.
        $earnedLog = ReferralRewardLog::factory()->state([
            'action' => ReferralRewardLog::ACTION_EARNED,
        ])->create();

        $expiredLog = ReferralRewardLog::factory()->state([
            'action' => ReferralRewardLog::ACTION_EXPIRED,
        ])->create();

        // Apply the action filter and confirm only the matching action remains visible in the table output.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->filterTable('action', ReferralRewardLog::ACTION_EARNED)
            ->assertCanSeeTableRecords([$earnedLog])
            ->assertCanNotSeeTableRecords([$expiredLog]);
    }
}
