<?php

declare(strict_types=1);

use App\Models\Price;
use App\Models\Product;
use Database\Seeders\PriceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('feature: seeds product prices for the admin listing', function (): void {
    expect(Price::count())->toBe(0);

    $seeder = new PriceSeeder;
    $seeder->run();

    expect(Price::count())->toBeGreaterThan(0);

    $price = Price::query()->with(['priceable', 'currency'])->first();

    expect($price)->not->toBeNull();
    expect($price?->priceable)->toBeInstanceOf(Product::class);
    expect($price?->currency)->not->toBeNull();
    expect((float) ($price?->amount ?? 0))->toBeGreaterThan(0);
});
