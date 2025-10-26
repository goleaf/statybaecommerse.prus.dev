<?php

declare(strict_types=1);

use Tests\TestCase;

uses(TestCase::class);

/**
 * @param class-string     $modelClass
 * @param non-empty-string $relation
 * @param class-string     $expectedRelationClass
 */
it('exposes expected relation accessors', function (string $modelClass, string $relation, string $expectedRelationClass): void {
    /** @var class-string $modelClass */
    $modelClass = $modelClass;

    $model = new $modelClass;

    /** @var class-string $expectedRelationClass */
    $expectedRelationClass = $expectedRelationClass;

    $relationInstance = $model->{$relation}();

    expect($relationInstance)->toBeInstanceOf($expectedRelationClass);
})->with('model_relation_matrix');
