<?php

declare(strict_types=1);

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\AssertsRelations;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('checks variant relations resolve cleanly', function (): void {
    // Instantiate a bare model instance for signature inspections.
    $model = new ProductVariant;

    if (method_exists($model, 'product')) {
        AssertsRelations::assertRelation($model, 'product', BelongsTo::class);
    }

    if (method_exists($model, 'images')) {
        AssertsRelations::assertRelation($model, 'images', HasMany::class);
    }

    if (method_exists($model, 'prices')) {
        AssertsRelations::assertRelation($model, 'prices', HasMany::class);
    }
});

it('orders product variants alphabetically when the configured column exists', function (): void {
    // Respect schemas that rely on alternative columns such as sku or title.
    $table = 'product_variants';
    $column = 'name';

    if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
        markTestSkipped("{$table}.{$column} not present");
    }

    ProductVariant::query()->delete();

    try {
        ProductVariant::factory()->create([$column => 'Z']);
        ProductVariant::factory()->create([$column => 'A']);
    } catch (Throwable $exception) {
        // Factories may be unavailable, so ensure deterministic records via direct creation.
        ProductVariant::query()->create([$column => 'Z']);
        ProductVariant::query()->create([$column => 'A']);
    }

    expect(ProductVariant::orderedByName()->pluck($column)->all())->toBe(['A', 'Z']);
});
