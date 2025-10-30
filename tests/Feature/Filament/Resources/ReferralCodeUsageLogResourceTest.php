<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralCodeUsageLogResource;
use App\Filament\Resources\ReferralCodeUsageLogResource\Pages\ListReferralCodeUsageLogs;
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

        // Resolve the Filament admin panel so components bootstrap before running Livewire tests.
        $this->resolveAdminPanel();

        // Normalise localisation for deterministic assertions in table snapshots.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Create and authenticate an administrator to bypass resource authorization policies.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_index_page_is_accessible(): void
    {
        // Smoke test the HTTP endpoint to ensure the Filament routing is registered correctly.
        $this
            ->get(ReferralCodeUsageLogResource::getUrl('index'))
            ->assertOk();
    }

    public function test_list_page_displays_usage_log_record(): void
    {
        // Persist a log entry with deterministic attributes so table assertions remain stable.
        $log = ReferralCodeUsageLog::factory()->create([
            'ip_address' => '198.51.100.42',
            'user_agent' => 'StatybaTestAgent/1.0',
        ]);

        // Hydrate the Livewire-powered table before verifying the seeded record is visible to administrators.
        Livewire::test(ListReferralCodeUsageLogs::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$log])
            ->assertSee('198.51.100.42')
            ->assertSee('StatybaTestAgent/1.0');
    }
}
