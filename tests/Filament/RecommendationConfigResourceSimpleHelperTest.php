<?php

declare(strict_types=1);

use App\Filament\Resources\RecommendationConfigResourceSimple;
use App\Models\Product;
use Tests\TestCase;

uses(TestCase::class);
uses()->group('filament');

it('normalises relation identifier payloads into sorted unique strings', function (): void {
    // Prepare a product model instance so we can confirm model identifiers are coerced into strings.
    $product = Product::make();
    $product->setAttribute('id', 4);

    // Seed a mixture of payload shapes to mirror how Filament combobox components may hydrate their state.
    $state = [
        $product,
        ['id' => 2],
        ['id' => '3'],
        ['identifier' => 'ignored'],
        ['0' => 1],
        '2',
        3,
        1.0,
        null,
        false,
        new stdClass(),
    ];

    // Invoke the helper and confirm the identifiers are string-cast, de-duplicated, sorted, and re-indexed.
    $normalised = RecommendationConfigResourceSimple::normaliseRelationIdentifiers($state);

    expect($normalised)->toBe(['1', '2', '3', '4']);
});

it('returns an empty array when no identifiers can be resolved', function (): void {
    // Provide empty and non-scalar inputs to simulate an unselected combobox payload.
    $state = [null, false, new stdClass()];

    // Ensure the helper gracefully collapses to an empty array instead of propagating placeholder values.
    $normalised = RecommendationConfigResourceSimple::normaliseRelationIdentifiers($state);

    expect($normalised)->toBeArray()->toBeEmpty();

    // Calling the helper with an explicit null should behave the same way for defensive callers.
    expect(RecommendationConfigResourceSimple::normaliseRelationIdentifiers(null))->toBeArray()->toBeEmpty();
});
