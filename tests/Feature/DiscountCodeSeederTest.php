<?php

declare(strict_types=1);

use App\Models\Discount;
use App\Models\DiscountCode;
use Database\Seeders\DiscountCodeSeeder;

it('generates discount codes for existing discounts via factories', function (): void {
    $discount = Discount::factory()->create();

    expect($discount->codes)->toBeEmpty();

    $this->seed(DiscountCodeSeeder::class);

    $discount->refresh();

    expect($discount->codes)->not->toBeEmpty();
    expect($discount->codes)->toHaveCount(20);

    $discount->codes->each(function (DiscountCode $code): void {
        expect($code->discount_id)->toBe($code->discount->id);
    });
});
