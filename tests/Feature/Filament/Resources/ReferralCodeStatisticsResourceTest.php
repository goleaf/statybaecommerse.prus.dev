<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralCodeStatisticsResource;
use App\Filament\Resources\ReferralCodeStatisticsResource\Pages\CreateReferralCodeStatistics;
use App\Filament\Resources\ReferralCodeStatisticsResource\Pages\ListReferralCodeStatistics;
use App\Models\ReferralCode;
use App\Models\ReferralCodeStatistics;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Establish a privileged administrator to satisfy Filament policy expectations.
    $this->admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('feature: loads referral code statistics index page', function (): void {
    // Confirm the analytics listing boots correctly for administrators.
    $this
        ->get(ReferralCodeStatisticsResource::getUrl('index'))
        ->assertOk();
});

it('feature: loads referral code statistics creation page', function (): void {
    // Ensure the analytics resource exposes the creation form without errors.
    $this
        ->get(ReferralCodeStatisticsResource::getUrl('create'))
        ->assertOk();
});

it('feature: creates referral code statistics via Livewire', function (): void {
    // Seed a referral code so the statistics entry can link to a concrete campaign.
    $referralCode = ReferralCode::factory()->create(['code' => 'ANALYTICS-001']);

    // Submit the analytics payload including deterministic metrics for easy verification.
    Livewire::test(CreateReferralCodeStatistics::class)
        ->fillForm([
            'referral_code_id'  => $referralCode->getKey(),
            'date'              => now()->format('Y-m-d'),
            'total_views'       => 150,
            'total_clicks'      => 45,
            'total_signups'     => 20,
            'total_conversions' => 10,
            'total_revenue'     => 250.75,
            'metadata'          => ['note' => 'performance snapshot'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Validate the persisted statistics row contains the expected metrics and relationships.
    $this->assertDatabaseHas('referral_code_statistics', [
        'referral_code_id'  => $referralCode->getKey(),
        'total_conversions' => 10,
        'total_revenue'     => 250.75,
    ]);
});

it('feature: lists referral code statistics records in the table', function (): void {
    // Generate analytics rows so the Livewire table renders non-empty metrics.
    $statistics = ReferralCodeStatistics::factory()->count(2)->create();

    // Ensure the administrator can see the seeded statistics rows inside the Filament table.
    Livewire::test(ListReferralCodeStatistics::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords($statistics);
});
