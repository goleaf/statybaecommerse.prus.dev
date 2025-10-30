<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ReferralStatisticsResource\Pages\ListReferralStatistics;
use App\Models\ReferralStatistics;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

final class ReferralStatisticsResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel configuration to mirror production routing within Livewire tests.
        $this->resolveAdminPanel();

        // Enforce English locales for predictable number and date formatting.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate an administrator matching Filament's guard expectations.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_referral_statistics(): void
    {
        // Seed a statistics row capturing aggregated referral totals for a user on a specific date.
        $statistics = ReferralStatistics::factory()->create([
            'user_id'             => $this->admin->id,
            'date'                => Carbon::parse('2025-01-01'),
            'total_referrals'     => 5,
            'completed_referrals' => 3,
            'pending_referrals'   => 2,
        ]);

        // Ensure the statistics record is visible when the Filament list component renders.
        Livewire::test(ListReferralStatistics::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$statistics]);
    }

    public function test_table_filters_statistics_by_user_and_date_range(): void
    {
        // Create statistics for two users across distinct dates to exercise the filters thoroughly.
        $matching = ReferralStatistics::factory()->create([
            'user_id' => $this->admin->id,
            'date'    => Carbon::parse('2025-02-15'),
        ]);

        $otherUser = User::factory()->create();

        $outsideRange = ReferralStatistics::factory()->create([
            'user_id' => $otherUser->id,
            'date'    => Carbon::parse('2024-12-31'),
        ]);

        // Apply the user and date range filters to focus the table on the matching statistics row only.
        Livewire::test(ListReferralStatistics::class)
            ->call('loadTable')
            ->filterTable('user_id', (string) $this->admin->id)
            ->filterTable('date_range', [
                'from'  => '2025-02-01',
                'until' => '2025-02-28',
            ])
            ->assertCanSeeTableRecords([$matching])
            ->assertCanNotSeeTableRecords([$outsideRange]);
    }
}
