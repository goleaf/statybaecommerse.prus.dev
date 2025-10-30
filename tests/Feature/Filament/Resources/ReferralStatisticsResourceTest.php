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

        // Ensure the Filament admin panel is active so resource pages bootstrap correctly.
        $this->resolveAdminPanel();

        // Align localisation with English to stabilise currency and badge formatting.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Provision an administrator account that satisfies Filament authorization requirements.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_index_page_shows_referral_statistics(): void
    {
        // Create an analyst user whose name will surface inside the statistics table.
        $analyst = User::factory()->create([
            'name'  => 'Referral Analyst',
            'email' => 'analyst@example.com',
        ]);

        // Seed aggregated statistics with deterministic values for the table columns.
        $statistics = ReferralStatistics::factory()
            ->for($analyst)
            ->create([
                'date'                  => '2024-01-01',
                'total_referrals'       => 5,
                'completed_referrals'   => 3,
                'pending_referrals'     => 2,
                'total_rewards_earned'  => 99.99,
                'total_discounts_given' => 12.34,
            ]);

        $this->actingAs($this->admin);

        // Sanity-check the HTTP route before asserting the Livewire data grid renders the seeded metrics.
        $this->get(ReferralStatisticsResource::getUrl('index'))->assertOk();

        Livewire::test(ListReferralStatistics::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$statistics])
            ->assertSee('Referral Analyst')
            ->assertSee('5')
            ->assertSee('3');
    }
}
