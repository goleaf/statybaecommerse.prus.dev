<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralCodeUsageLogs\Pages\EditReferralCodeUsageLog;
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

        // Ensure the Filament admin panel is fully booted so Livewire components resolve their dependencies.
        $this->resolveAdminPanel();

        // Authenticate as a deterministic admin user to satisfy Filament authorization checks.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_usage_logs(): void
    {
        // Seed a referral code and member to generate a concrete usage log entry.
        $referralCode = ReferralCode::factory()->create([
            'code' => 'LIST-COVERAGE',
        ]);
        $participant = User::factory()->create([
            'name' => 'Usage Participant',
        ]);

        $log = ReferralCodeUsageLog::factory()
            ->for($referralCode, 'referralCode')
            ->for($participant, 'user')
            ->create([
                'ip_address' => '198.51.100.10',
                'referrer'   => 'https://example.test/campaign',
            ]);

        Livewire::test(ListReferralCodeUsageLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$log]);
    }

    public function test_filters_allow_limiting_by_referral_code_and_user(): void
    {
        // Prepare deterministic referral codes so the filter dropdowns render reproducible options.
        $trackedCode = ReferralCode::factory()->create([
            'code' => 'FILTER-MATCH',
        ]);
        $ignoredCode = ReferralCode::factory()->create([
            'code' => 'FILTER-SKIP',
        ]);
        $targetUser = User::factory()->create([
            'name' => 'Target User',
        ]);
        $otherUser = User::factory()->create([
            'name' => 'Other User',
        ]);

        $matchingLog = ReferralCodeUsageLog::factory()
            ->for($trackedCode, 'referralCode')
            ->for($targetUser, 'user')
            ->create([
                'ip_address' => '198.51.100.20',
            ]);

        $nonMatchingLog = ReferralCodeUsageLog::factory()
            ->for($ignoredCode, 'referralCode')
            ->for($otherUser, 'user')
            ->create([
                'ip_address' => '198.51.100.30',
            ]);

        Livewire::test(ListReferralCodeUsageLogs::class)
            ->call('loadTable')
            ->filterTable('referral_code_id', (string) $trackedCode->getKey())
            ->filterTable('user_id', (string) $targetUser->getKey())
            ->call('loadTable')
            ->assertCanSeeTableRecords([$matchingLog])
            ->assertCanNotSeeTableRecords([$nonMatchingLog]);
    }

    public function test_edit_form_updates_core_tracking_fields(): void
    {
        // Create a baseline usage log so the edit form has hydrated relationships to work with.
        $referralCode = ReferralCode::factory()->create([
            'code' => 'EDIT-CODE',
        ]);
        $participant = User::factory()->create([
            'name' => 'Editable User',
        ]);

        $log = ReferralCodeUsageLog::factory()
            ->for($referralCode, 'referralCode')
            ->for($participant, 'user')
            ->create([
                'ip_address' => '203.0.113.10',
                'user_agent' => 'LegacyAgent/1.0',
            ]);

        Livewire::test(EditReferralCodeUsageLog::class, ['record' => $log->getKey()])
            ->fillForm([
                // Update the networking metadata to ensure the form persists changes correctly.
                'ip_address' => '203.0.113.55',
                'user_agent' => 'UpdatedAgent/2.0',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('referral_code_usage_logs', [
            'id'         => $log->getKey(),
            'ip_address' => '203.0.113.55',
            'user_agent' => 'UpdatedAgent/2.0',
        ]);
    }
}
