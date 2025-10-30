<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

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

        // Boot the Filament panel so global resources, navigation, and Livewire hooks are available in assertions.
        $this->resolveAdminPanel();

        // Sign in an administrator to satisfy authorization gates enforced by the resource pages.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_renders_statistics_snapshot(): void
    {
        // Provision a referrer to anchor the aggregated statistics entry within the listing table.
        $referrer = User::factory()->create([
            'email' => 'referrer.stats@example.com',
        ]);

        // Store a snapshot with fixed numeric totals so the Livewire table can surface predictable values.
        $statistics = ReferralStatistics::factory()->for($referrer)->create([
            'date'                  => now()->toDateString(),
            'total_referrals'       => 7,
            'completed_referrals'   => 5,
            'pending_referrals'     => 2,
            'total_rewards_earned'  => 99.95,
            'total_discounts_given' => 45.50,
        ]);

        Livewire::test(ListReferralStatistics::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$statistics]);
    }
}
