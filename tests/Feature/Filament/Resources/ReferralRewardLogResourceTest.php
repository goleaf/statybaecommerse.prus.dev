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
        'email'    => 'reward-log-admin@example.test',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('lists reward logs even when user agent is null', function (): void {
    $reward = ReferralReward::factory()->create([
        'user_id' => $this->admin->id,
    ]);

    $log = ReferralRewardLog::factory()->create([
        'referral_reward_id' => $reward->id,
        'user_id'            => $this->admin->id,
        'user_agent'         => null,
    ]);

    Livewire::actingAs($this->admin)
        ->test(ListReferralRewardLogs::class)
        ->assertCanSeeTableRecords([$log]);
});

it('keeps referral reward log compatibility pages registered', function (): void {
    expect(ReferralRewardLogResource::getPages())->toHaveKeys(['index', 'create', 'edit']);
});
