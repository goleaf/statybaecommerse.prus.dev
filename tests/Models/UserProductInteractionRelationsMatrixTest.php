<?php

declare(strict_types=1);

use App\Models\UserProductInteraction;
use Tests\TestCase;

uses(TestCase::class);

it('maps documented UserProductInteraction relations to their expected relation types', function (string $relation, string $expectedRelationType, string $expectedRelatedModel): void {
    $interaction = new UserProductInteraction;

    $relationInstance = $interaction->{$relation}();

    expect($relationInstance)
        ->toBeInstanceOf($expectedRelationType)
        ->and($relationInstance->getRelated())
        ->toBeInstanceOf($expectedRelatedModel);
})->with('user_product_interaction_relations_matrix');
