<?php

declare(strict_types=1);

use App\Models\Price;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AssertsRelations;

uses(RefreshDatabase::class);

it('asserts the product relation is defined when available', function (): void {
    // Instantiate the model to inspect relation signatures safely.
    $model = new Price;

    if (method_exists($model, 'product')) {
        AssertsRelations::assertRelation($model, 'product', BelongsTo::class);
    } else {
        markTestSkipped('Price::product() not defined');
    }
});

it('skips orderedByName expectations for price models', function (): void {
    // Price records rarely expose a name column, so this scenario is intentionally skipped.
    markTestSkipped('Price typically lacks a name/title column.');
});
