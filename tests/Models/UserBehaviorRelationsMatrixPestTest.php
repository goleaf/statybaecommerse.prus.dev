<?php

declare(strict_types=1);

use App\Models\UserBehavior;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

uses(TestCase::class);

// Dataset enumerating the expected relations on UserBehavior so they remain documented.
dataset('user_behavior_relations_matrix', [
    ['user', BelongsTo::class],
]);

it('maps documented UserBehavior relations to their expected relation types', function (string $relation, string $expectedType): void {
    $model = new UserBehavior;

    expect($model->{$relation}())->toBeInstanceOf($expectedType);
})->with('user_behavior_relations_matrix');
