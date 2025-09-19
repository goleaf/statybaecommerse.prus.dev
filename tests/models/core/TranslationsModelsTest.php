<?php declare(strict_types=1);

use App\Models\Translations\AttributeTranslation;
use App\Models\Translations\AttributeValueTranslation;
use App\Models\Translations\BrandTranslation;
use App\Models\Translations\CategoryTranslation;
use App\Models\Translations\CollectionTranslation;
use App\Models\Translations\LegalTranslation;
use App\Models\Translations\ProductTranslation;

it('instantiates translation models', function (): void {
    expect(new LegalTranslation())
        ->toBeInstanceOf(LegalTranslation::class)
        ->and(new AttributeValueTranslation())
        ->toBeInstanceOf(AttributeValueTranslation::class)
        ->and(new AttributeTranslation())
        ->toBeInstanceOf(AttributeTranslation::class)
        ->and(new BrandTranslation())
        ->toBeInstanceOf(BrandTranslation::class)
        ->and(new CollectionTranslation())
        ->toBeInstanceOf(CollectionTranslation::class)
        ->and(new CategoryTranslation())
        ->toBeInstanceOf(CategoryTranslation::class)
        ->and(new ProductTranslation())
        ->toBeInstanceOf(ProductTranslation::class);
});
