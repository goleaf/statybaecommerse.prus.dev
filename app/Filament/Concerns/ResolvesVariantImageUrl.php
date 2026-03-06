<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Models\ProductVariant;

trait ResolvesVariantImageUrl
{
    protected static function resolveVariantImageUrl(ProductVariant $record): ?string
    {
        $variantImageUrl = $record->getFirstMediaUrl('images', 'thumb');

        if ($variantImageUrl !== '') {
            return $variantImageUrl;
        }

        $product = $record->product;

        if ($product === null) {
            return null;
        }

        return $product->getMainImage('thumb') ?? $product->getMainImage();
    }
}
