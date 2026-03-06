<?php

declare(strict_types=1);

use App\Filament\Resources\Referrals\Pages\EditReferral;
use App\Filament\Resources\Referrals\RelationManagers\ReferralStatisticsRelationManager;
use App\Filament\Resources\ReferralStatistics\ReferralStatisticsResource;
use App\Filament\Resources\ReferralStatisticsResource\Pages\ListReferralStatistics;
use App\Models\Referral;
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

it('can delete referral statistics from the referral relation manager', function (): void {
    $referrer = User::factory()->create();
    $referral = Referral::factory()->active()->create([
        'referrer_id' => $referrer->id,
    ]);

    $ownedStatistic = ReferralStatistics::factory()->create([
        'user_id' => $referrer->id,
    ]);
    $unrelatedStatistic = ReferralStatistics::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(ReferralStatisticsRelationManager::class, [
            'ownerRecord' => $referral,
            'pageClass'   => EditReferral::class,
        ])
        ->assertCanSeeTableRecords([$ownedStatistic])
        ->assertCanNotSeeTableRecords([$unrelatedStatistic])
        ->callTableAction('delete', $ownedStatistic)
        ->assertHasNoFormErrors();

    $this->assertDatabaseMissing('referral_statistics', [
        'id' => $ownedStatistic->id,
    ]);
    $this->assertDatabaseHas('referral_statistics', [
        'id' => $unrelatedStatistic->id,
    ]);
});

it('can bulk delete referral statistics from the referral relation manager', function (): void {
    $referrer = User::factory()->create();
    $referral = Referral::factory()->active()->create([
        'referrer_id' => $referrer->id,
    ]);

    $ownedStatistics = ReferralStatistics::factory()->count(2)->create([
        'user_id' => $referrer->id,
    ]);
    $unrelatedStatistic = ReferralStatistics::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(ReferralStatisticsRelationManager::class, [
            'ownerRecord' => $referral,
            'pageClass'   => EditReferral::class,
        ])
        ->assertCanSeeTableRecords($ownedStatistics)
        ->assertCanNotSeeTableRecords([$unrelatedStatistic])
        ->callTableBulkAction('delete', $ownedStatistics)
        ->assertHasNoFormErrors();

    foreach ($ownedStatistics as $statistic) {
        $this->assertDatabaseMissing('referral_statistics', [
            'id' => $statistic->id,
        ]);
    }

    $this->assertDatabaseHas('referral_statistics', [
        'id' => $unrelatedStatistic->id,
    ]);
});

it('does not register referral statistics resource in sidebar navigation', function (): void {
    expect(ReferralStatisticsResource::shouldRegisterNavigation())->toBeFalse();
});
