<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\AssertsRelations;
use Tests\TestCase;

// Boot the full Laravel test case so schema and configuration facades resolve during model assertions.
uses(TestCase::class, RefreshDatabase::class);

it('orders users alphabetically when the name column exists', function (): void {
    // Skip when the backing schema does not expose a name column for the user table.
    if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'name')) {
        markTestSkipped('users.name column not present');
    }

    User::query()->delete();

    try {
        User::factory()->create(['name' => 'Zoe']);
        User::factory()->create(['name' => 'Anna']);
    } catch (Throwable) {
        // Provide a guarded fallback when factories are disabled in the project configuration.
        User::query()->create(['name' => 'Zoe', 'email' => 'zoe@example.test', 'password' => bcrypt('secret')]);
        User::query()->create(['name' => 'Anna', 'email' => 'anna@example.test', 'password' => bcrypt('secret')]);
    }

    expect(User::orderedByName()->pluck('name')->all())->toBe(['Anna', 'Zoe']);
});

it('ensures optional user relations resolve to supported relation classes', function (): void {
    // Create a baseline model instance purely for relation signature checks.
    $model = new User;

    if (method_exists($model, 'roles')) {
        AssertsRelations::assertRelation($model, 'roles', BelongsToMany::class);
    }

    if (method_exists($model, 'customer')) {
        $relation = $model->customer();
        // Accept either HasOne or BelongsTo implementations depending on domain requirements.
        expect($relation)->toBeInstanceOf(Relation::class);
        expect(class_basename($relation))->toBeIn(['HasOne', 'BelongsTo']);
    }

    if (method_exists($model, 'orders')) {
        AssertsRelations::assertRelation($model, 'orders', HasMany::class);
    }

    if (method_exists($model, 'wishlistItems')) {
        AssertsRelations::assertRelation($model, 'wishlistItems', HasMany::class);
    }
});
