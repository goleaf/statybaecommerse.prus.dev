<?php

declare(strict_types=1);

use App\Support\Filament\SearchableComponentHelper;
use App\Support\Search\SearchResultPayload;
use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;

// This regression test ensures clearing the component wipes both the display option and stored payload metadata.
it('clears the searchable component state when the selection is removed', function (): void {
    $component = SearchableInput::make('product_id');

    $result = SearchResultPayload::normalise(
        SearchResult::make('42', 'Demo Product'),
        [
            'product_id' => 42,
            'sku'        => 'SKU-42',
            'name'       => 'Demo Product',
            'price'      => 99.0,
        ],
    );

    SearchableComponentHelper::hydrate(
        component: $component,
        state: 42,
        resolveResult: static fn (): SearchResult => $result,
    );

    expect($component->getOptions())->toHaveCount(1);

    $captured = [];

    SearchableComponentHelper::sync(
        component: $component,
        state: '',
        set: function (string $field, $value) use (&$captured): void {
            $captured[$field] = $value;
        },
        targetField: 'product_id',
        resolveResult: static fn (): SearchResult => $result,
    );

    expect($captured['product_id'] ?? null)
        ->toBeNull()
        ->and($component->getOptions())
        ->toBe([])
        ->and($component->getState())
        ->toBeNull();
});
