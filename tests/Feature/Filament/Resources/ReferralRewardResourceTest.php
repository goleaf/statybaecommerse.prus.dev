<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('lists referral rewards for administrators', function (): void {
    // Arrange: create an administrator and a visible referral reward with deterministic copy.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $reward = ReferralReward::factory()->create([
        'title' => [
            'en' => 'Welcome Bonus',
            'lt' => 'Sveikinimo Premija',
        ],
        'description' => [
            'en' => 'Bonus granted for early adopters.',
            'lt' => 'Premija ankstyviems naudotojams.',
        ],
        'reward_data' => [
            'category' => 'discount',
        ],
    ]);

    // Act: authenticate as the admin so Filament policies allow the page to render.
    actingAs($admin);

    // Assert: ensure the Livewire table hydrates and shows the seeded reward entry.
    Livewire::test(ListReferralRewards::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$reward]);
});

it('applies referral rewards via the table action', function (): void {
    // Arrange: provision an administrator and a pending reward awaiting application.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $reward = ReferralReward::factory()->pending()->create([
        'title' => [
            'en' => 'Apply Me Bonus',
            'lt' => 'Pritaikoma premija',
        ],
        'reward_data' => [
            'category' => 'discount',
        ],
    ]);

    // Act: impersonate the admin and trigger the table action that marks the reward as applied.
    actingAs($admin);

    Livewire::test(ListReferralRewards::class)
        ->call('loadTable')
        ->callTableAction('apply', $reward)
        ->assertHasNoTableActionErrors();

    // Assert: confirm that the reward status and timestamp reflect the apply action.
    $reward->refresh();

    expect($reward->status)->toBe('applied')
        ->and($reward->applied_at)->not->toBeNull();
});

it('expires referral rewards through the bulk action', function (): void {
    // Arrange: create an admin and multiple active rewards to exercise the bulk expire workflow.
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $pendingRewards = ReferralReward::factory()->count(2)->create([
        'status' => 'pending',
        'reward_data' => [
            'category' => 'discount',
        ],
    ]);

    // Act: authenticate and execute the bulk expire action against the collection.
    actingAs($admin);

    Livewire::test(ListReferralRewards::class)
        ->call('loadTable')
        ->callTableBulkAction('expire', $pendingRewards)
        ->assertHasNoTableBulkActionErrors();

    // Assert: verify that each reward captured by the bulk action is now marked as expired.
    $pendingRewards->each(function (ReferralReward $reward): void {
        $reward->refresh();

        expect($reward->status)->toBe('expired');
    });
});
