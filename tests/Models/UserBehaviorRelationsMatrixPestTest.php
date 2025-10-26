<?php

declare(strict_types=1);

use App\Models\UserBehavior;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

uses(TestCase::class);

// Dataset enumerating the expected relations on UserBehavior so they remain documented.
dataset('user_behavior_relations_matrix', [
    // User relationship keeps behaviour analytics anchored to the account owner.
    ['user', BelongsTo::class],
    // Product relationship maps engagements back to catalogue items for interaction analytics.
    ['product', BelongsTo::class],
    // Category relationship supports higher-level merchandising insights.
    ['category', BelongsTo::class],
]);

it('maps documented UserBehavior relations to their expected relation types', function (string $relation, string $expectedType): void {
    $model = new UserBehavior;

    expect($model->{$relation}())->toBeInstanceOf($expectedType);
})->with('user_behavior_relations_matrix');
