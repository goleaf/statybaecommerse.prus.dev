<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralRewardLogResource\Pages\ListReferralRewardLogs;
use App\Filament\Resources\ReferralRewardLogs\ReferralRewardLogResource;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
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

it('lists referral reward logs in the compatibility list page', function (): void {
    $reward = ReferralReward::factory()->create([
        'user_id' => $this->admin->id,
    ]);

    $log = ReferralRewardLog::factory()->create([
        'referral_reward_id' => $reward->id,
        'user_id'            => $this->admin->id,
        'action'             => ReferralRewardLog::ACTION_EARNED,
    ]);

    Livewire::actingAs($this->admin)
        ->test(ListReferralRewardLogs::class)
        ->assertCanSeeTableRecords([$log]);
});

it('does not register referral reward log resource in sidebar navigation', function (): void {
    expect(ReferralRewardLogResource::shouldRegisterNavigation())->toBeFalse();
});
