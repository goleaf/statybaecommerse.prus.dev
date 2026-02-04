<?php

declare(strict_types=1);

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\DocumentTemplateResource;

it('positions document templates after categories in admin navigation', function () {
    $categorySort = CategoryResource::getNavigationSort();
    $documentTemplateSort = DocumentTemplateResource::getNavigationSort();

    expect($categorySort)->not->toBeNull();
    expect($documentTemplateSort)->not->toBeNull();
    expect($documentTemplateSort)->toBeGreaterThan($categorySort);
});
