<?php

declare(strict_types=1);

use App\Filament\Resources\ProductSimilarityResource;

it('unit: hides ProductSimilarityResource from navigation', function () {
    expect(ProductSimilarityResource::shouldRegisterNavigation())->toBeFalse();
});
