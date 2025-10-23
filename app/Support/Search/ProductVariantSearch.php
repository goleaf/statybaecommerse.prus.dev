<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Product;
use App\Models\ProductVariant;
use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class ProductVariantSearch
{
    /**
     * @return array<int, SearchResult>
     */
    public static function results(string $term, int $limit = 15): array
    {
        /** @var EloquentCollection<int, ProductVariant> $variants */
        $variants = ProductVariant::query()
            ->select(['id', 'product_id', 'name', 'sku', 'price'])
            ->with(['product:id,name,sku'])
            ->when(trim($term) !== '', function (Builder $builder) use ($term): void {
                $builder->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%")
                        ->orWhereHas('product', function (Builder $productQuery) use ($term): void {
                            $productQuery
                                ->where('name', 'like', "%{$term}%")
                                ->orWhere('sku', 'like', "%{$term}%");
                        });
                });
            })
            ->orderByDesc('updated_at')
            ->limit($limit)
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

                // Bundle both the variant details and parent product context into the payload.
                return SearchResultPayload::normalise($result, [
                    'variant_id'   => $variant->getKey(),
                    'sku'          => $sku,
                    'name'         => $name,
                    'price'        => $price,
                    'product_id'   => $variant->getAttribute('product_id'),
                    'product_sku'  => $productSku,
                    'product_name' => $productName,
                ]);
            })
            ->all();
    }

    public static function label(ProductVariant $variant): string
    {
        $product = $variant->getRelationValue('product');

        $productName = $product instanceof Product
            ? self::resolveProductName($product)
            : '';

        /** @var string|null $rawVariantName */
        $rawVariantName = $variant->getAttribute('name');
        /** @var string|null $rawSku */
        $rawSku = $variant->getAttribute('sku');

        $variantName = $rawVariantName ?? '';
        $sku = $rawSku ?? '—';

        return trim(sprintf('[%s] %s — %s', $sku !== '' ? $sku : '—', $productName, $variantName));
    }

    public static function payload(ProductVariant $variant): array
    {
        $product = $variant->getRelationValue('product');

        $productName = $product instanceof Product
            ? self::resolveProductName($product)
            : '';

        /** @var float|int|string|null $rawPrice */
        $rawPrice = $variant->getAttribute('price');
        $price = is_numeric($rawPrice) ? (float) $rawPrice : 0.0;

        return [
            'variant_id'   => $variant->getKey(),
            'product_id'   => $variant->getAttribute('product_id'),
            'sku'          => (string) ($variant->getAttribute('sku') ?? ''),
            'name'         => (string) ($variant->getAttribute('name') ?? ''),
            'product_name' => $productName,
            'price'        => $price,
        ];
    }

    public static function hydrateComponent(SearchableInput $component, ?int $state): void
    {
        if ($state === null) {
            SearchableComponentHelper::forget($component);

            return;
        }

        $variant = ProductVariant::query()
            ->select(['id', 'product_id', 'sku', 'name', 'price'])
            ->with(['product:id,sku,name'])
            ->find($state);

        if (! $variant instanceof ProductVariant) {
            return;
        }

        SearchableComponentHelper::apply($component, self::toResult($variant));
    }

    /**
     * @return Builder<ProductVariant>
     */
    private static function query(string $term): Builder
    {
        $variant = ProductVariant::query()
            ->select(['id', 'product_id', 'name', 'sku', 'price'])
            ->with(['product:id,name,sku'])
            ->find($variantId);

        if (! $variant instanceof ProductVariant) {
            return null;
        }

        return self::payload($variant);
    }

    private static function resolveProductName(Product $product): string
    {
        /** @var string|null $rawName */
        $rawName = $product->getAttribute('name');

        if (is_array($rawName)) {
            $locale = app()->getLocale();
            $value = $rawName[$locale] ?? reset($rawName);

            return is_string($value) ? $value : '';
        }

        return is_string($rawName) ? $rawName : '';
    }

    private static function toResult(ProductVariant $variant): SearchResult
    {
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
    }
}
