<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralCodeUsageLogResource\Pages\ListReferralCodeUsageLogs;
use App\Models\ReferralCode;
use App\Models\ReferralCodeUsageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralCodeUsageLogResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel so Livewire pages bootstrap the correct panel context.
        $this->resolveAdminPanel();

        // Normalise locales to keep translated attributes deterministic in assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Seed a reusable admin user so every request automatically passes authorization checks.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_list_page_displays_usage_logs(): void
    {
        // Create a named customer to guarantee friendly labels in the table output.
        $customer = User::factory()->create([
            'name'  => 'Referral Tracker',
            'email' => 'tracker@example.com',
        ]);

        // Generate a predictable referral code so copyable column assertions stay readable.
        $referralCode = ReferralCode::factory()
            ->for($customer)
            ->withCode('TRACKME')
            ->create();

        // Persist the usage log with deterministic networking metadata for explicit visibility checks.
        $usageLog = ReferralCodeUsageLog::factory()
            ->withUser($customer)
            ->withReferralCode($referralCode)
            ->fromIp('203.0.113.10')
            ->withUserAgent('Test Agent/1.0')
            ->withReferrer('https://example.com/campaign')
            ->create();

        $this->actingAs($this->admin);

        // Hydrate the Livewire table and assert the seeded log is rendered with key column values.
        Livewire::test(ListReferralCodeUsageLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$usageLog])
            ->assertSee('Referral Tracker')
            ->assertSee('203.0.113.10');
    }
}
