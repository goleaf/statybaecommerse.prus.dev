<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralRewardResource\Pages\CreateReferralReward;
use App\Filament\Resources\ReferralRewardResource\Pages\EditReferralReward;
use App\Filament\Resources\ReferralRewards\ReferralRewardResource;
use App\Models\Referral;
use App\Models\ReferralReward;
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

it('does not register referral reward resource in sidebar navigation', function (): void {
    expect(ReferralRewardResource::shouldRegisterNavigation())->toBeFalse();
});

it('creates a referral reward from the compatibility create page', function (): void {
    $referral = Referral::factory()->create([
        'referrer_id' => $this->admin->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(CreateReferralReward::class)
        ->fillForm([
            'referral_id'   => $referral->id,
            'user_id'       => $this->admin->id,
            'type'          => 'discount',
            'amount'        => 10,
            'currency_code' => 'EUR',
            'status'        => 'pending',
            'title'         => 'Launch Discount',
            'description'   => 'Reward issued for new referral signups.',
            'is_active'     => true,
            'priority'      => 5,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(
        ReferralReward::query()
            ->where('user_id', $this->admin->id)
            ->where('type', 'discount')
            ->where('status', 'pending')
            ->exists()
    )->toBeTrue();
});

it('updates referral reward values from the compatibility edit page', function (): void {
    $reward = ReferralReward::factory()->create([
        'user_id' => $this->admin->id,
        'status'  => 'pending',
        'type'    => 'credit',
        'amount'  => 5,
        'title'   => ['lt' => 'Original Reward'],
    ]);

    Livewire::actingAs($this->admin)
        ->test(EditReferralReward::class, ['record' => $reward->getRouteKey()])
        ->fillForm([
            'user_id'  => $this->admin->id,
            'type'     => 'discount',
            'amount'   => 25.5,
            'status'   => 'pending',
            'title'    => 'Updated Reward',
            'priority' => 3,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $reward->refresh();

    expect($reward->type)->toBe('discount');
    expect((string) $reward->amount)->toBe('25.50');
    expect($reward->getTranslation('title', 'lt'))->toBe('Updated Reward');
});
