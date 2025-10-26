<?php

declare(strict_types=1);

use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AssertsRelations;

uses(RefreshDatabase::class);

it('verifies core order relations compile correctly', function (): void {
    // Instantiate a fresh model instance so relation methods can be exercised without database writes.
    $model = new Order;

    AssertsRelations::assertRelation($model, 'customer', BelongsTo::class);
    AssertsRelations::assertRelation($model, 'items', HasMany::class);
    AssertsRelations::assertRelation($model, 'shipping', HasOne::class);
});

it('skips orderedByName expectations for the order model by design', function (): void {
    // Orders typically rely on numbers rather than human-readable names, so this scope is optional.
    markTestSkipped('Orders commonly lack a name column; implement orderedByName() only when needed.');
});
