<?php

declare(strict_types=1);

use App\Models\Referral;
use App\Models\User;
use App\Services\ReferralRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores referrer bonus rewards in eur even when another currency is provided', function (): void {
    $service = app(ReferralRewardService::class);

    $referrer = User::factory()->create();
    $referred = User::factory()->create();

    $referral = Referral::query()->create([
        'referrer_id'   => $referrer->id,
        'referred_id'   => $referred->id,
        'referral_code' => 'REF-EUR-1001',
        'status'        => 'pending',
    ]);

    $reward = $service->createReferrerBonus(
        referralId: $referral->id,
        userId: $referrer->id,
        amount: 25.00,
        rewardData: [
            'currency' => 'USD',
            'category' => 'credit',
        ],
    );

    expect($reward)->not->toBeNull();
    expect($reward?->currency_code)->toBe('EUR');

    $this->assertDatabaseHas('referral_rewards', [
        'id'            => $reward?->id,
        'currency_code' => 'EUR',
    ]);
});

it('stores tiered referral rewards in eur even when tier currency is not eur', function (): void {
    config([
        'referral.reward_tiers' => [
            ['threshold' => 0, 'category' => 'points', 'amount' => 50.0, 'currency' => 'PTS'],
        ],
    ]);

    $service = app(ReferralRewardService::class);

    $referrer = User::factory()->create();
    $referred = User::factory()->create();

    $referral = Referral::query()->create([
        'referrer_id'   => $referrer->id,
        'referred_id'   => $referred->id,
        'referral_code' => 'REF-EUR-1002',
        'status'        => 'pending',
    ]);

    $reward = $service->createTieredReferrerReward($referral);

    expect($reward)->not->toBeNull();
    expect($reward?->currency_code)->toBe('EUR');
});
