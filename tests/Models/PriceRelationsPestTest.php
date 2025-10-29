<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AssertsRelations;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('asserts the product relation is defined when available', function (): void {
    // Instantiate the model to inspect relation signatures safely.
    $model = new Price;

    // The product relation must always exist so callers can traverse price->product consistently.
    AssertsRelations::assertRelation($model, 'product', BelongsTo::class);
});

it('orders prices by translated name for deterministic admin listings', function (): void {
    // Provision the related product and currency so the created prices are valid records.
    $product = Product::factory()->create();
    $currency = Currency::factory()->create(['code' => 'EUR']);

    // Create two price records with translations intentionally inserted out of alphabetical order.
    $beta = Price::factory()->create([
        'priceable_type' => $product->getMorphClass(),
        'priceable_id'   => $product->getKey(),
        'currency_id'    => $currency->getKey(),
    ]);
    $beta->translations()->create([
        'locale'      => 'en',
        'name'        => 'Beta tier',
        'description' => 'Second alphabetically',
    ]);

    $alpha = Price::factory()->create([
        'priceable_type' => $product->getMorphClass(),
        'priceable_id'   => $product->getKey(),
        'currency_id'    => $currency->getKey(),
    ]);
    $alpha->translations()->create([
        'locale'      => 'en',
        'name'        => 'Alpha tier',
        'description' => 'First alphabetically',
    ]);

    // Confirm the orderedByName scope sorts the ids based on the translated name and supports desc ordering.
    expect(Price::query()->orderedByName('asc', 'en')->pluck('id')->all())->toBe([$alpha->id, $beta->id]);
    expect(Price::query()->orderedByName('desc', 'en')->pluck('id')->all())->toBe([$beta->id, $alpha->id]);
});
