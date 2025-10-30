<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralRewardResource;
use App\Filament\Resources\ReferralRewardResource\Pages\CreateReferralReward;
use App\Filament\Resources\ReferralRewardResource\Pages\ListReferralRewards;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Provision an administrator account so Filament policy checks succeed by default.
    $this->admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    // Authenticate every test request as the privileged administrator.
    $this->actingAs($this->admin);
});

it('feature: loads referral reward index page', function (): void {
    // Hit the resource index endpoint to confirm the Filament listing bootstraps without errors.
    $this
        ->get(ReferralRewardResource::getUrl('index'))
        ->assertOk();
});

it('feature: loads referral reward creation page', function (): void {
    // Visit the create page to ensure the schema renders for administrators.
    $this
        ->get(ReferralRewardResource::getUrl('create'))
        ->assertOk();
});

it('feature: creates a referral reward through the form action', function (): void {
    // Seed a user who will own the reward so the relationship dropdown resolves.
    $rewardOwner = User::factory()->create();

    // Submit the create form with deterministic reward metadata.
    Livewire::test(CreateReferralReward::class)
        ->fillForm([
            'user_id'       => $rewardOwner->getKey(),
            'type'          => 'credit',
            'amount'        => 25,
            'currency_code' => 'EUR',
            'status'        => 'pending',
            'title'         => 'Signup credit',
            'description'   => 'Reward for inviting a new shopper.',
            'is_active'     => true,
            'priority'      => 1,
            'conditions'    => ['orders_min' => '1'],
            'reward_data'   => ['bonus' => 'free shipping'],
            'metadata'      => ['source' => 'referral-program'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Confirm the referral reward row persisted using the submitted payload.
    $this->assertDatabaseHas('referral_rewards', [
        'title->en' => 'Signup credit',
        'status'    => 'pending',
        'priority'  => 1,
    ]);
});

it('feature: applies a referral reward using the table action', function (): void {
    // Create a pending reward record that the table action will transition to the applied state.
    $reward = ReferralReward::factory()->create([
        'status' => 'pending',
    ]);

    // Invoke the dedicated table action and verify the status flag flips accordingly.
    Livewire::test(ListReferralRewards::class)
        ->call('loadTable')
        ->callTableAction('apply', $reward)
        ->assertHasNoTableActionErrors();

    // Reload the model to assert the state mutation performed by the action.
    expect($reward->refresh()->status)->toBe('applied');
});
