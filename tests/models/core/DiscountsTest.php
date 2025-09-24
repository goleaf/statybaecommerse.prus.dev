<?php

declare(strict_types=1);

use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountCondition;
use App\Models\DiscountRedemption;

it('instantiates Discount models', function (): void {
    expect(new Discount)
        ->toBeInstanceOf(Discount::class)
        ->and(new DiscountCode)
        ->toBeInstanceOf(DiscountCode::class)
        ->and(new DiscountCondition)
        ->toBeInstanceOf(DiscountCondition::class)
        ->and(new DiscountRedemption)
        ->toBeInstanceOf(DiscountRedemption::class);
});
