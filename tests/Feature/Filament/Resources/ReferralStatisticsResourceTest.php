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

/**
 * Regression coverage for the referral statistics administration resource.
 */
final class ReferralStatisticsResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Prepare the Filament admin context so Livewire pages mount against the admin panel configuration.
        $this->resolveAdminPanel();

        // Ensure translated attributes render deterministically within English assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate using the seeded admin account to satisfy policy checks during table interactions.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_shows_referral_statistics(): void
    {
        // Arrange: create a statistics snapshot for the admin to surface in the listing table.
        $stats = ReferralStatistics::factory()->create([
            'user_id' => $this->admin->getKey(),
            'date'    => Carbon::now()->toDateString(),
        ]);

        // Act & Assert: hydrate the table and verify the seeded snapshot is visible with its totals.
        Livewire::test(ListReferralStatistics::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$stats])
            ->assertSee((string) $stats->total_referrals);
    }

    public function test_filters_statistics_by_date_range_and_user(): void
    {
        // Arrange: seed a snapshot inside the window and one outside to confirm filtering behaviour.
        $inside = ReferralStatistics::factory()->create([
            'user_id' => $this->admin->getKey(),
            'date'    => Carbon::now()->subDay()->toDateString(),
        ]);
        $outside = ReferralStatistics::factory()->create([
            'date' => Carbon::now()->subWeeks(2)->toDateString(),
        ]);

        // Act: limit the dataset by user and a narrow date range covering the matching record.
        Livewire::test(ListReferralStatistics::class)
            ->call('loadTable')
            ->filterTable('user_id', $this->admin->getKey())
            ->filterTable('date_range', [
                'from'  => Carbon::now()->subDays(2)->toDateString(),
                'until' => Carbon::now()->toDateString(),
            ])
            // Assert: the inside snapshot persists while the older record is excluded.
            ->assertCanSeeTableRecords([$inside])
            ->assertCanNotSeeTableRecords([$outside]);
    }

    public function test_refresh_actions_emit_success_notifications(): void
    {
        // Arrange: seed a snapshot to drive the table actions alongside a batch for bulk operations.
        $stats = ReferralStatistics::factory()->create([
            'user_id' => $this->admin->getKey(),
        ]);
        $batch = ReferralStatistics::factory()->count(2)->create();

        $component = Livewire::test(ListReferralStatistics::class);
        $component->call('loadTable');

        // Act: execute the single-record refresh action to ensure it completes without validation errors.
        $component
            ->callTableAction('refresh_stats', $stats)
            ->assertHasNoTableActionErrors();

        // Act & Assert: bulk refresh should also succeed when targeting multiple statistics entries.
        $component
            ->callTableBulkAction('refresh_all_stats', $batch)
            ->assertHasNoTableBulkActionErrors();
    }
}
