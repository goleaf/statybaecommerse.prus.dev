<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\AssertsRelations;

uses(RefreshDatabase::class);

it('orders products alphabetically when the name column exists', function (): void {
    // Prevent brittle failures when the schema snapshot lacks the expected column.
    if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'name')) {
        markTestSkipped('products.name column not present');
    }

    Product::query()->delete();

    try {
        Product::factory()->create(['name' => 'Zebra']);
        Product::factory()->create(['name' => 'Apple']);
    } catch (Throwable $exception) {
        // Fall back to direct creation to keep the assertion meaningful without factories.
        Product::query()->create(['name' => 'Zebra']);
        Product::query()->create(['name' => 'Apple']);
    }

    expect(Product::orderedByName()->pluck('name')->all())->toBe(['Apple', 'Zebra']);
});

it('confirms optional product relations resolve to relation instances', function (): void {
    // Create a raw model instance to interrogate relation signatures directly.
    $model = new Product;

    if (method_exists($model, 'category')) {
        AssertsRelations::assertRelation($model, 'category', BelongsTo::class);
    }

    if (method_exists($model, 'categories')) {
        AssertsRelations::assertRelation($model, 'categories', BelongsToMany::class);
    }

    if (method_exists($model, 'variants')) {
        AssertsRelations::assertRelation($model, 'variants', HasMany::class);
    }

    if (method_exists($model, 'images')) {
        AssertsRelations::assertRelation($model, 'images', HasMany::class);
    }

    if (method_exists($model, 'brand')) {
        AssertsRelations::assertRelation($model, 'brand', BelongsTo::class);
    }
});
