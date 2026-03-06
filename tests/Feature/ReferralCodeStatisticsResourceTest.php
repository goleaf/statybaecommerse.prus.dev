<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralCodeStatistics\ReferralCodeStatisticsResource;
use App\Filament\Resources\ReferralCodeStatisticsResource\Pages\ListReferralCodeStatistics;
use App\Models\ReferralCode;
use App\Models\ReferralCodeStatistics;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    $this->admin = User::factory()->create([
        'email'    => 'info@egisstatyba.lt',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('lists referral code statistics in the compatibility list page', function (): void {
    $code = ReferralCode::factory()->create();
    $records = ReferralCodeStatistics::factory()->count(2)->for($code, 'referralCode')->create();

    Livewire::actingAs($this->admin)
        ->test(ListReferralCodeStatistics::class)
        ->assertCanSeeTableRecords($records);
});

it('does not register referral code statistics resource in sidebar navigation', function (): void {
    expect(ReferralCodeStatisticsResource::shouldRegisterNavigation())->toBeFalse();
});
