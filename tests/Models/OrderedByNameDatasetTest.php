<?php

declare(strict_types=1);

use App\Models\Concerns\OrdersByName;
use App\Models\PriceListItem;

// Dataset ensures models expected to expose the shared orderedByName scope are tracked centrally.
dataset('ordered_by_name_models', fn () => [
    PriceListItem::class,
]);

it('models in the OrderedByName dataset include the shared trait', function (string $className): void {
    $usedTraits = class_uses_recursive($className);

    expect($usedTraits)
        ->toBeArray()
        ->and(in_array(OrdersByName::class, $usedTraits, true))
        ->toBeTrue();
})->with('ordered_by_name_models');
