<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralCodeUsageLogResource\Pages\CreateReferralCodeUsageLog;
use App\Filament\Resources\ReferralCodeUsageLogResource\Pages\ListReferralCodeUsageLogs;
use App\Models\ReferralCode;
use App\Models\ReferralCodeUsageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Feature coverage for the Filament v4 referral code usage log resource screens.
 */
final class ReferralCodeUsageLogResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ReferralCode $referralCode;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the admin panel so Filament boots the correct panel configuration for each test.
        $this->resolveAdminPanel();

        // Force deterministic English locale output for factories and translated attributes.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Seed an administrator account that can access the resource screens without authorization failures.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Create a reusable referral code so usage logs can reference a stable parent record across assertions.
        $this->referralCode = ReferralCode::factory()->create([
            'code'        => 'FILAMENT-COVERAGE',
            'description' => 'Filament v4 coverage seed',
        ]);

        // Authenticate the seeded administrator for every HTTP and Livewire interaction.
        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_usage_logs(): void
    {
        // Persist a usage log with contextual metadata that should appear in the listing table.
        $log = ReferralCodeUsageLog::factory()->create([
            'referral_code_id' => $this->referralCode->getKey(),
            'user_id'          => User::factory()->create()->getKey(),
            'ip_address'       => '198.51.100.42',
            'user_agent'       => null,
            'metadata'         => ['device' => 'mobile'],
        ]);

        // Load the table data explicitly before asserting the seeded record is visible to the administrator.
        Livewire::test(ListReferralCodeUsageLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$log])
            ->assertCanSeeTableColumnStateSet('ip_address', $log->ip_address);
    }

    public function test_can_create_usage_log_from_form_action(): void
    {
        // Provision a deterministic user so the form payload references a concrete related model.
        $user = User::factory()->create(['email' => 'referral-user@example.com']);

        // Submit the creation form and ensure the Livewire action succeeds without validation errors.
        Livewire::test(CreateReferralCodeUsageLog::class)
            ->fillForm([
                'referral_code_id' => (string) $this->referralCode->getKey(),
                'user_id'          => (string) $user->getKey(),
                'ip_address'       => '203.0.113.99',
                'referrer'         => 'https://prus.dev/campaign',
                'user_agent'       => 'Mozilla/5.0 (X11; Linux x86_64)',
                'metadata'         => [
                    'device'  => 'desktop',
                    'browser' => 'Firefox',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Verify the database contains the created usage log alongside the submitted metadata payload.
        $createdLog = ReferralCodeUsageLog::query()->latest('id')->first();
        $this->assertNotNull($createdLog);
        $this->assertSame($this->referralCode->getKey(), $createdLog->referral_code_id);
        $this->assertSame($user->getKey(), $createdLog->user_id);
        $this->assertSame('203.0.113.99', $createdLog->ip_address);
        $this->assertSame([
            'device'  => 'desktop',
            'browser' => 'Firefox',
        ], $createdLog->metadata);
    }

    public function test_table_filters_by_referral_code_and_user(): void
    {
        // Create a log that should remain visible once both filters are applied to the listing table.
        $matchingUser = User::factory()->create(['email' => 'filter-user@example.com']);
        $matchingLog = ReferralCodeUsageLog::factory()->create([
            'referral_code_id' => $this->referralCode->getKey(),
            'user_id'          => $matchingUser->getKey(),
        ]);

        // Create a decoy log so the filter assertion confirms unrelated records are excluded.
        $otherLog = ReferralCodeUsageLog::factory()->create();

        // Apply both table filters and ensure only the matching log remains visible in the dataset.
        Livewire::test(ListReferralCodeUsageLogs::class)
            ->call('loadTable')
            ->filterTable('referral_code_id', $this->referralCode->getKey())
            ->filterTable('user_id', $matchingUser->getKey())
            ->assertCanSeeTableRecords([$matchingLog])
            ->assertCanNotSeeTableRecords([$otherLog]);
    }
}
