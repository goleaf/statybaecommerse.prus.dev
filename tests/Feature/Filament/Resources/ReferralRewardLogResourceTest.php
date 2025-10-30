<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralRewardLogResource;
use App\Filament\Resources\ReferralRewardLogResource\Pages\CreateReferralRewardLog;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Authenticate as an administrator so resource policies always pass during assertions.
    $this->admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('feature: loads referral reward log index page', function (): void {
    // Verify the listing route renders successfully for privileged operators.
    $this
        ->get(ReferralRewardLogResource::getUrl('index'))
        ->assertOk();
});

it('feature: loads referral reward log creation page', function (): void {
    // Ensure the create form is reachable so administrators can seed audit records.
    $this
        ->get(ReferralRewardLogResource::getUrl('create'))
        ->assertOk();
});

it('feature: creates a referral reward log entry', function (): void {
    // Prepare the underlying reward and user so the select inputs expose viable options.
    $reward = ReferralReward::factory()->create();
    $actor = User::factory()->create(['name' => 'Reward Reviewer']);

    // Submit the creation form using deterministic metadata for predictable assertions.
    Livewire::test(CreateReferralRewardLog::class)
        ->fillForm([
            'referral_reward_id' => $reward->getKey(),
            'user_id'            => $actor->getKey(),
            'action'             => ReferralRewardLog::ACTION_EARNED,
            'ip_address'         => '203.0.113.10',
            'user_agent'         => 'StatybaTestAgent/1.0',
            'data'               => json_encode(['note' => 'reward issued']),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Confirm the persisted audit record links back to the expected reward and user.
    $this->assertDatabaseHas('referral_reward_logs', [
        'referral_reward_id' => $reward->getKey(),
        'user_id'            => $actor->getKey(),
        'action'             => ReferralRewardLog::ACTION_EARNED,
        'ip_address'         => '203.0.113.10',
    ]);
});
