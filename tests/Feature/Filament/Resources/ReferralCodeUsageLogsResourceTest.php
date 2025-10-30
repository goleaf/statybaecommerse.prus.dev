<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralCodeUsageLogs\Pages\ListReferralCodeUsageLogs;
use App\Models\ReferralCode;
use App\Models\ReferralCodeUsageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralCodeUsageLogsResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Boot the Filament admin panel to ensure Livewire pages resolve with the correct tenancy and configuration.
        $this->resolveAdminPanel();

        // Pin the locale so translated relationships and metadata render consistently across assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Provision an administrator capable of accessing the referral code usage log resource pages.
        $this->admin = User::factory()->create([
            'email'    => 'code-usage-admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_renders_usage_log_with_copyable_columns(): void
    {
        // Create a referral code with a deterministic string so we can assert that the copyable column is rendered.
        $referralCode = ReferralCode::factory()->create(['code' => 'COPY-123']);

        // Persist a usage log linked to the referral code and user, including a lengthy user agent to exercise truncation logic.
        $log = ReferralCodeUsageLog::factory()
            ->for($referralCode, 'referralCode')
            ->for(User::factory()->create(['name' => 'Usage Log Viewer']), 'user')
            ->create([
                'user_agent' => str_repeat('LongUserAgent ', 3),
                'ip_address' => '198.51.100.24',
            ]);

        // Ensure the hydrated table exposes both the referral code value and associated user name to the administrator.
        Livewire::actingAs($this->admin)
            ->test(ListReferralCodeUsageLogs::class)
            ->call('loadTable')
            ->assertTableColumnExists('referralCode.code')
            ->assertCanSeeTableRecords([$log])
            ->assertSee('Usage Log Viewer');
    }

    public function test_filters_limit_results_to_selected_referral_code_and_user(): void
    {
        // Prepare a specific referral code and user to act as the matching criteria for the table filters.
        $referralCode = ReferralCode::factory()->create(['code' => 'FILTER-001']);
        $user = User::factory()->create(['name' => 'Filter Target User']);

        // Seed logs so that only one matches both the target referral code and user combination.
        $matchingLog = ReferralCodeUsageLog::factory()
            ->for($referralCode, 'referralCode')
            ->for($user, 'user')
            ->create();
        $nonMatchingLog = ReferralCodeUsageLog::factory()->create();

        // Apply the table filters and confirm the mismatched record disappears from the result set.
        Livewire::actingAs($this->admin)
            ->test(ListReferralCodeUsageLogs::class)
            ->call('loadTable')
            ->filterTable('referral_code_id', $referralCode->id)
            ->filterTable('user_id', $user->id)
            ->assertCanSeeTableRecords([$matchingLog])
            ->assertCanNotSeeTableRecords([$nonMatchingLog]);
    }

    public function test_bulk_delete_action_removes_selected_usage_logs(): void
    {
        // Create a set of usage logs so we can verify that the bulk delete operation only removes the targeted entries.
        $logs = ReferralCodeUsageLog::factory()->count(2)->create();

        // Execute the delete bulk action against the first record and ensure the component reports no validation issues.
        Livewire::actingAs($this->admin)
            ->test(ListReferralCodeUsageLogs::class)
            ->call('loadTable')
            ->callTableBulkAction('delete', [$logs[0]])
            ->assertHasNoTableBulkActionErrors();

        // Confirm the targeted record is removed while the second log remains present in persistent storage.
        $this->assertDatabaseMissing('referral_code_usage_logs', ['id' => $logs[0]->id]);
        $this->assertDatabaseHas('referral_code_usage_logs', ['id' => $logs[1]->id]);
    }
}
