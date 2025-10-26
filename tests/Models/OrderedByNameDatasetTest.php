<?php

declare(strict_types=1);

use App\Models\Concerns\OrdersByName;

it('models in the OrderedByName dataset include the shared trait', function (string $className, array $columnCandidates): void {
    $usedTraits = class_uses_recursive($className);

    expect($usedTraits)
        ->toBeArray()
        ->and(in_array(OrdersByName::class, $usedTraits, true))
        ->toBeTrue();

    expect($columnCandidates)->toBeArray();
    expect($columnCandidates)->not->toBeEmpty();
})->with('ordered_by_name_models');
