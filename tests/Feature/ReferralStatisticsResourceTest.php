<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralStatistics\ReferralStatisticsResource;
use App\Filament\Resources\ReferralStatisticsResource\Pages\ListReferralStatistics;
use App\Models\ReferralStatistics;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    $this->admin = User::factory()->create([
        'email'    => 'referral-stats-admin@example.test',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('lists referral statistics in the compatibility list page', function (): void {
    $records = ReferralStatistics::factory()->count(2)->create();

    Livewire::actingAs($this->admin)
        ->test(ListReferralStatistics::class)
        ->assertCanSeeTableRecords($records);
});

it('filters referral statistics by user', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $recordA = ReferralStatistics::factory()->create(['user_id' => $userA->id]);
    $recordB = ReferralStatistics::factory()->create(['user_id' => $userB->id]);

    Livewire::actingAs($this->admin)
        ->test(ListReferralStatistics::class)
        ->filterTable('user_id', $userA->id)
        ->assertCanSeeTableRecords([$recordA])
        ->assertCanNotSeeTableRecords([$recordB]);
});

it('does not register referral statistics resource in sidebar navigation', function (): void {
    expect(ReferralStatisticsResource::shouldRegisterNavigation())->toBeFalse();
});
