<?php

declare(strict_types=1);

use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use Database\Seeders\DiscountRedemptionSeeder;

it('feature: creates redemptions for discount codes via factories', function (): void {
    /** @var Discount $discount */
    $discount = Discount::factory()
        // Explicitly bind generated codes to the "codes" relationship so the factory
        // does not attempt to infer a singular "discountCode" relation name.
        ->has(DiscountCode::factory()->count(2), 'codes')
        ->create();

    /** @var \Illuminate\Database\Eloquent\Collection<int, DiscountRedemption> $initialRedemptions */
    $initialRedemptions = $discount->redemptions;

    expect($initialRedemptions)->toBeEmpty();

    /** @var \Illuminate\Foundation\Testing\TestCase $this */
    $this->seed(DiscountRedemptionSeeder::class);

    $discount->refresh();

    /** @var \Illuminate\Database\Eloquent\Collection<int, DiscountRedemption> $redemptions */
    $redemptions = $discount->redemptions;

    expect($redemptions)->not->toBeEmpty();

    $redemptions->each(function (DiscountRedemption $redemption): void {
        expect($redemption->discount)->not->toBeNull();
        expect($redemption->code)->not->toBeNull();
        expect($redemption->user)->not->toBeNull();
        expect($redemption->order)->not->toBeNull();
    });
});
