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

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament boots the admin panel so the Livewire list page resolves its dependencies.
        $this->resolveAdminPanel();

        // Keep localisation deterministic for assertions that rely on translated column headings.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Sign in an administrator to satisfy the resource's authorization requirements.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_logs_and_respects_filters(): void
    {
        // Create a baseline referral reward and participant so the filters have concrete relationships to target.
        $reward = ReferralReward::factory()->create();
        $user = $reward->user ?? User::factory()->create();

        // Attach one log that should remain visible and a contrasting record that filters should hide.
        $visibleLog = ReferralRewardLog::factory()->create([
            'referral_reward_id' => $reward->getKey(),
            'user_id'            => $user->getKey(),
            'action'             => ReferralRewardLog::ACTION_EARNED,
            'created_at'         => now()->subDay(),
        ]);

        $hiddenLog = ReferralRewardLog::factory()->create([
            'action'     => ReferralRewardLog::ACTION_CANCELLED,
            'created_at' => now()->subWeeks(3),
        ]);

        // Hydrate the table data and walk through each filter to confirm only the qualifying record remains.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$visibleLog, $hiddenLog])
            ->filterTable('action', ReferralRewardLog::ACTION_EARNED)
            ->assertCanSeeTableRecords([$visibleLog])
            ->assertCanNotSeeTableRecords([$hiddenLog])
            ->filterTable('user_id', $user->getKey())
            ->assertCanSeeTableRecords([$visibleLog])
            ->filterTable('referral_reward_id', $reward->getKey())
            ->assertCanSeeTableRecords([$visibleLog]);
    }

    public function test_table_actions_and_bulk_delete_are_available(): void
    {
        // Seed a concise log entry whose relationships are eager loaded for action visibility checks.
        $log = ReferralRewardLog::factory()->create([
            'action' => ReferralRewardLog::ACTION_REDEEMED,
        ]);

        // Confirm the view and edit actions surface for individual records.
        Livewire::test(ListReferralRewardLogs::class)
            ->call('loadTable')
            ->assertTableActionVisible('view', $log)
            ->assertTableActionVisible('edit', $log)
            // Execute the delete bulk action and ensure the record is removed without surfacing errors.
            ->callTableBulkAction('delete', [$log])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing($log->getTable(), [
            'id' => $log->getKey(),
        ]);
    }

    public function test_navigation_metadata_uses_translation_keys(): void
    {
        // The resource should expose the configured translation-backed labels for navigation scaffolding.
        self::assertSame(__('admin.referral_reward_logs.navigation_label'), ReferralRewardLogResource::getNavigationLabel());
        self::assertSame(__('admin.referral_reward_logs.model_label'), ReferralRewardLogResource::getModelLabel());
        self::assertSame(__('admin.referral_reward_logs.plural_model_label'), ReferralRewardLogResource::getPluralModelLabel());
    }
}
