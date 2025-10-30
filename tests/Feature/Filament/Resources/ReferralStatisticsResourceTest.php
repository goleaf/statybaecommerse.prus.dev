<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralStatisticsResource;
use App\Filament\Resources\ReferralStatisticsResource\Pages\ListReferralStatistics;
use App\Models\ReferralStatistics;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralStatisticsResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Register Filament resources before running Livewire-driven assertions against the panel.
        $this->resolveAdminPanel();

        // Align application locale output with deterministic English fixtures.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Create and sign in an administrator to bypass referral statistics authorization.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_index_page_is_accessible(): void
    {
        // Validate that the referral statistics listing route resolves successfully.
        $this
            ->get(ReferralStatisticsResource::getUrl('index'))
            ->assertOk();
    }

    public function test_list_page_displays_referral_statistics_record(): void
    {
        // Persist aggregated statistics with meaningful totals so the table renders human-friendly insights.
        $statistics = ReferralStatistics::factory()->create([
            'user_id'               => $this->admin->id,
            'date'                  => now()->toDateString(),
            'total_referrals'       => 5,
            'completed_referrals'   => 3,
            'pending_referrals'     => 2,
            'total_rewards_earned'  => 150.25,
            'total_discounts_given' => 75.10,
        ]);

        // Hydrate the Livewire table and ensure the aggregated counts and currency figures are visible.
        Livewire::test(ListReferralStatistics::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$statistics])
            ->assertSee('5')
            ->assertSee('150.25');
    }
}
