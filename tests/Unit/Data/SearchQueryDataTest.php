<?php

declare(strict_types=1);

use App\Data\SearchQueryData;

test('search query data normalizes case-insensitive type filters', function (): void {
    // Reproduces a bug where clients sent mixed-case types and the filter silently fell back to every bucket.
    $data = SearchQueryData::fromArray([
        'query' => 'desk lamp',
        'types' => ['Product', 'CATEGORY', ' brand '],
    ]);


    expect($data->types())->toBe(['product', 'category', 'brand']);
});
