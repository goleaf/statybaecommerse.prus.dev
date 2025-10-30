<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralStatisticsResource;
use App\Filament\Resources\ReferralStatisticsResource\Pages\CreateReferralStatistics;
use App\Filament\Resources\ReferralStatisticsResource\Pages\ListReferralStatistics;
use App\Models\ReferralStatistics;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Authenticate an administrator so the resource policies authorize every request.
    $this->admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('feature: loads referral statistics index page', function (): void {
    // Confirm the analytics dashboard list view renders without issues.
    $this
        ->get(ReferralStatisticsResource::getUrl('index'))
        ->assertOk();
});

it('feature: loads referral statistics creation page', function (): void {
    // Ensure the creation form is available for building new analytics entries.
    $this
        ->get(ReferralStatisticsResource::getUrl('create'))
        ->assertOk();
});

it('feature: creates referral statistics via Livewire form', function (): void {
    // Prepare a subject user so the statistics entry attaches to a concrete account.
    $subject = User::factory()->create(['name' => 'Referral Analyst']);

    // Submit the analytics payload with explicit metric values for precise verification.
    Livewire::test(CreateReferralStatistics::class)
        ->fillForm([
            'user_id'               => $subject->getKey(),
            'date'                  => now()->format('Y-m-d'),
            'total_referrals'       => 12,
            'completed_referrals'   => 8,
            'pending_referrals'     => 4,
            'total_rewards_earned'  => 320.50,
            'total_discounts_given' => 150.25,
            'metadata'              => ['segment' => 'loyalty'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Confirm the stored statistics reflect the supplied metrics for the tracked user.
    $this->assertDatabaseHas('referral_statistics', [
        'user_id'               => $subject->getKey(),
        'total_referrals'       => 12,
        'completed_referrals'   => 8,
        'total_rewards_earned'  => 320.50,
    ]);
});

it('feature: lists referral statistics records within the table', function (): void {
    // Generate analytics fixtures so the Filament table renders multiple records.
    $records = ReferralStatistics::factory()->count(2)->create();

    // Load the table component and confirm the seeded rows are visible to administrators.
    Livewire::test(ListReferralStatistics::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords($records);
});
