<?php

declare(strict_types=1);

use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use Database\Seeders\DiscountRedemptionSeeder;

it('creates redemptions for discount codes via factories', function (): void {
    $discount = Discount::factory()
        ->has(DiscountCode::factory()->count(2))
        ->create();

    expect($discount->redemptions)->toBeEmpty();

    $this->seed(DiscountRedemptionSeeder::class);

    $discount->refresh();

    expect($discount->redemptions)->not->toBeEmpty();

    $discount->redemptions->each(function (DiscountRedemption $redemption): void {
        expect($redemption->discount)->not->toBeNull();
        expect($redemption->code)->not->toBeNull();
        expect($redemption->user)->not->toBeNull();
        expect($redemption->order)->not->toBeNull();
    });
});
