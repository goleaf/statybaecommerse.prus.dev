<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralStatisticsResource\Pages\ListReferralStatistics;
use App\Models\ReferralStatistics;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('lists referral statistics for administrators', function (): void {
    // Arrange: seed an administrator and a statistics record anchored to the current date.
    Carbon::setTestNow('2025-03-15 12:00:00');
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $stats = ReferralStatistics::factory()->create([
        'date' => Carbon::today()->toDateString(),
        'total_referrals' => 5,
        'total_rewards_earned' => 42.50,
    ]);

    // Act: authenticate so the Filament component can mount with elevated permissions.
    actingAs($admin);

    // Assert: confirm the table hydration surfaces the seeded statistics entry.
    Livewire::test(ListReferralStatistics::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$stats]);

    Carbon::setTestNow();
});

it('filters referral statistics by date range', function (): void {
    // Arrange: create admin context and statistics entries straddling the desired filter window.
    Carbon::setTestNow('2025-03-15 12:00:00');
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $insideRange = ReferralStatistics::factory()->create([
        'date' => '2025-03-10',
    ]);
    $outsideRange = ReferralStatistics::factory()->create([
        'date' => '2025-02-28',
    ]);

    // Act: impersonate the admin and apply the inclusive date range filter.
    actingAs($admin);

    Livewire::test(ListReferralStatistics::class)
        ->call('loadTable')
        ->filterTable('date_range', [
            'from' => '2025-03-01',
            'until' => '2025-03-31',
        ])
        ->call('loadTable')
        ->assertCanSeeTableRecords([$insideRange])
        ->assertCanNotSeeTableRecords([$outsideRange]);

    Carbon::setTestNow();
});

it('filters referral statistics for meaningful reward totals', function (): void {
    // Arrange: provision an admin with contrasting statistics totals for the rewards filter.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $rewardingStats = ReferralStatistics::factory()->create([
        'total_rewards_earned' => 25.00,
    ]);
    $emptyStats = ReferralStatistics::factory()->create([
        'total_rewards_earned' => 0.00,
    ]);

    // Act: sign in and enable the has_rewards filter to focus on positive totals.
    actingAs($admin);

    Livewire::test(ListReferralStatistics::class)
        ->call('loadTable')
        ->filterTable('has_rewards', true)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$rewardingStats])
        ->assertCanNotSeeTableRecords([$emptyStats]);

    Carbon::setTestNow();
});

it('refreshes referral statistics via the table action', function (): void {
    // Arrange: create admin and statistics record to trigger the refresh action safely.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $stats = ReferralStatistics::factory()->create();

    // Act: impersonate the admin and call the custom refresh action on the record.
    actingAs($admin);

    Livewire::test(ListReferralStatistics::class)
        ->call('loadTable')
        ->callTableAction('refresh_stats', $stats)
        ->assertHasNoTableActionErrors();

    // Assert: ensure the record still exists, signalling the action completed without mutation errors.
    expect($stats->fresh())->not->toBeNull();
});

