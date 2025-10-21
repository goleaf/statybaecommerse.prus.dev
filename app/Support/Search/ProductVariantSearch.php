<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Product;
use App\Models\ProductVariant;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;

final class ProductVariantSearch
{
    /**
     * @return array<int, SearchResult>
     */
    public static function results(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ProductVariant> $variants */
        $variants = self::query($term)
            ->limit($limit)
            ->with(['product:id,sku,name'])
            ->get();

        return $variants
            ->map(static function (ProductVariant $variant): SearchResult {
                /** @var int|string|null $identifier */
                $identifier = $variant->getKey();

                /** @var string|null $rawName */
                $rawName = $variant->getAttribute('name');
                /** @var string|null $rawSku */
                $rawSku = $variant->getAttribute('sku');
                /** @var float|int|string|null $rawPrice */
                $rawPrice = $variant->getAttribute('price');

                $name = $rawName ?? '';
                $sku = $rawSku ?? '';
                $price = is_numeric($rawPrice) ? (float) $rawPrice : 0.0;

                $product = $variant->getRelationValue('product');
                $productName = $product instanceof Product ? self::resolveName($product->getAttribute('name')) : '';
                $productSku = $product instanceof Product ? (string) ($product->getAttribute('sku') ?? '') : '';

                $labelFragments = array_filter([
                    $sku !== '' ? $sku : null,
                    $name !== '' ? $name : null,
                    $productName !== '' ? __('orders.lookups.variant_product', ['product' => $productName]) : null,
                ]);

                $label = trim(implode(' • ', $labelFragments));

                $result = SearchResult::make((string) ($identifier ?? ''), $label !== '' ? $label : __('orders.lookups.variant_unknown'));

                $result
                    ->withData('variant_id', $variant->getKey())
                    ->withData('sku', $sku)
                    ->withData('name', $name)
                    ->withData('price', $price)
                    ->withData('product_id', $variant->getAttribute('product_id'))
                    ->withData('product_sku', $productSku)
                    ->withData('product_name', $productName);

                return $result;
            })
            ->all();
    }

    public static function label(ProductVariant $variant): string
    {
        /** @var string|null $rawName */
        $rawName = $variant->getAttribute('name');
        /** @var string|null $rawSku */
        $rawSku = $variant->getAttribute('sku');

        $name = $rawName ?? '';
        $sku = $rawSku ?? '';
        $product = $variant->getRelationValue('product');
        $productName = $product instanceof Product ? self::resolveName($product->getAttribute('name')) : '';

        $labelFragments = array_filter([
            $sku !== '' ? $sku : null,
            $name !== '' ? $name : null,
            $productName !== '' ? __('orders.lookups.variant_product', ['product' => $productName]) : null,
        ]);

        $label = trim(implode(' • ', $labelFragments));

        return $label !== '' ? $label : __('orders.lookups.variant_unknown');
    }

    /**
     * @return Builder<ProductVariant>
     */
    private static function query(string $term): Builder
    {
        $search = trim($term);

        return ProductVariant::query()
            ->select(['id', 'product_id', 'sku', 'name', 'price'])
            ->when($search !== '', static function (Builder $builder) use ($search): void {
                $builder->where(static function (Builder $query) use ($search): void {
                    $query
                        ->where('sku', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('variant_name_lt', 'like', "%{$search}%")
                        ->orWhere('variant_name_en', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at');
    }

    private static function resolveName(mixed $value): string
    {
        if (is_array($value)) {
            $locale = app()->getLocale();

            $localized = $value[$locale] ?? collect($value)
                ->filter(static fn ($candidate): bool => is_string($candidate))
                ->first();

            return is_string($localized) ? $localized : '';
        }

        if (is_string($value)) {
            return $value;
        }

        return '';
    }
}

