<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Product;
use App\Models\ProductVariant;
use DefStudio\SearchableInput\DTO\SearchResult;
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
            ->map(function (ProductVariant $variant): SearchResult {
                $identifier = (string) $variant->getKey();
                $result = SearchResult::make($identifier, self::label($variant));

                $payload = self::payload($variant);

                return $result
                    ->withData('variant_id', $variant->getKey())
                    ->withData('product_id', $variant->getAttribute('product_id'))
                    ->withData('sku', $payload['sku'])
                    ->withData('name', $payload['name'])
                    ->withData('product_name', $payload['product_name'])
                    ->withData('price', $payload['price'])
                    ->withData('payload', $payload);
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

    public static function hydrate(int $variantId): ?array
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
}
