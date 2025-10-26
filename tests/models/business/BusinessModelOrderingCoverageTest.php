<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Campaign;
use App\Models\Concerns\OrdersByName;

/**
 * Matrix connecting business models to their expected alphabetical ordering behaviour.
 */
dataset('business_model_ordering_matrix', [
    // Brands must expose predictable alphabetical sorting inside storefront and admin listings.
    [Brand::class, 'name', null],
    // Campaigns prioritise scheduling recency over names, so we document the intentional exclusion.
    [Campaign::class, null, 'Campaign dashboards prioritise timeline views, so alphabetical ordering would mislead operations.'],
]);

it('tracks business model ordering expectations', function (string $className, ?string $expectedColumn, ?string $exclusionReason): void {
    // Capture the trait usage graph so we can assert coverage without manually instantiating every dependency.
    $usedTraits = class_uses_recursive($className);
    $usesOrdersByName = in_array(OrdersByName::class, $usedTraits, true);

    if ($expectedColumn !== null) {
        /** @var object{nameColumn?: string} $model */
        $model = new $className;
        $resolvedColumn = property_exists($model, 'nameColumn') ? $model->nameColumn : 'name';

        expect($usesOrdersByName)
            ->toBeTrue()
            ->and($resolvedColumn)
            ->toBe($expectedColumn);

        return;
    }

    expect($usesOrdersByName)->toBeFalse();
    expect($exclusionReason)->not->toBeNull();
    expect($exclusionReason)->not->toBe('');
})->with('business_model_ordering_matrix');
