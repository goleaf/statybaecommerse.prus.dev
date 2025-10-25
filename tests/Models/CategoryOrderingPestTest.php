<?php

declare(strict_types=1);

use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\AssertsRelations;

uses(RefreshDatabase::class);

it('orders categories alphabetically when the name column exists', function (): void {
    // Ensure the schema is compatible before attempting to assert ordering.
    if (! Schema::hasTable('categories') || ! Schema::hasColumn('categories', 'name')) {
        markTestSkipped('categories.name column not present');
    }

    Category::query()->delete();

    try {
        Category::factory()->create(['name' => 'Z']);
        Category::factory()->create(['name' => 'A']);
    } catch (Throwable $exception) {
        // Some projects omit factories, so we seed manually when necessary.
        Category::query()->create(['name' => 'Z']);
        Category::query()->create(['name' => 'A']);
    }

    expect(Category::orderedByName()->pluck('name')->all())->toBe(['A', 'Z']);
});

it('confirms category relations resolve to expected relation types', function (): void {
    // Spawn a model instance purely for relation inspection.
    $model = new Category;

    if (method_exists($model, 'parent')) {
        AssertsRelations::assertRelation($model, 'parent', BelongsTo::class);
    }

    if (method_exists($model, 'children')) {
        AssertsRelations::assertRelation($model, 'children', HasMany::class);
    }

    if (method_exists($model, 'products')) {
        AssertsRelations::assertRelation($model, 'products', BelongsToMany::class);
    }
});
