<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralRewardLogResource\Pages\ListReferralRewardLogs;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('lists referral reward logs for administrators', function (): void {
    // Arrange: create an admin user and an earned log entry to populate the listing.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $reward = ReferralReward::factory()->create([
        'reward_data' => [
            'category' => 'discount',
        ],
    ]);
    $log = ReferralRewardLog::factory()->for($reward, 'referralReward')->earned()->create([
        'ip_address' => '203.0.113.10',
        'user_agent' => 'CoverageBrowser/1.0',
    ]);

    // Act: authenticate as the admin so the Livewire component can mount successfully.
    actingAs($admin);

    // Assert: confirm the hydrated table exposes the expected log entry.
    Livewire::test(ListReferralRewardLogs::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$log]);
});

it('filters referral reward logs by action', function (): void {
    // Arrange: provision an admin and multiple log actions to exercise the filter dropdown.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $earnedReward = ReferralReward::factory()->create([
        'reward_data' => [
            'category' => 'discount',
        ],
    ]);
    $expiredReward = ReferralReward::factory()->create([
        'reward_data' => [
            'category' => 'discount',
        ],
    ]);
    $earnedLog = ReferralRewardLog::factory()->for($earnedReward, 'referralReward')->earned()->create();
    $expiredLog = ReferralRewardLog::factory()->for($expiredReward, 'referralReward')->expired()->create();

    // Act: sign in and apply the action filter for earned rewards only.
    actingAs($admin);

    Livewire::test(ListReferralRewardLogs::class)
        ->call('loadTable')
        ->filterTable('action', ReferralRewardLog::ACTION_EARNED)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$earnedLog])
        ->assertCanNotSeeTableRecords([$expiredLog]);
});

it('filters referral reward logs by related users', function (): void {
    // Arrange: create a specific user so the select filter has a deterministic option.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $targetUser = User::factory()->create([
        'name' => 'Referral Recipient',
    ]);
    $reward = ReferralReward::factory()->create([
        'reward_data' => [
            'category' => 'discount',
        ],
    ]);
    $visibleLog = ReferralRewardLog::factory()->for($targetUser, 'user')->for($reward, 'referralReward')->create([
        'action' => ReferralRewardLog::ACTION_REDEEMED,
    ]);
    $hiddenLog = ReferralRewardLog::factory()->for(ReferralReward::factory()->create([
        'reward_data' => [
            'category' => 'discount',
        ],
    ]), 'referralReward')->create();

    // Act: authenticate and apply the user filter to isolate the matching log entry.
    actingAs($admin);

    Livewire::test(ListReferralRewardLogs::class)
        ->call('loadTable')
        ->filterTable('user_id', $targetUser->getKey())
        ->call('loadTable')
        ->assertCanSeeTableRecords([$visibleLog])
        ->assertCanNotSeeTableRecords([$hiddenLog]);
});
