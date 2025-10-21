<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Product;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;

final class ProductSearch
{
    /**
     * @return array<int, string>
     */
    public static function byFreeText(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Product> $products */
        $products = self::baseQuery($term)
            ->limit($limit)
            ->get();

        return $products
            ->map(fn (Product $product): string => self::formatLabel($product))
            ->all();
    }

    /**
     * @return array<int, SearchResult>
     */
    public static function complex(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Product> $products */
        $products = self::baseQuery($term)
            ->limit($limit)
            ->get();

        return $products
            ->map(function (Product $product): SearchResult {
                /** @var int|string|null $identifier */
                $identifier = $product->getKey();
                $result = SearchResult::make((string) ($identifier ?? ''), self::formatLabel($product));

                /** @var string|null $rawSku */
                $rawSku = $product->getAttribute('sku');
                /** @var float|int|string|null $rawPrice */
                $rawPrice = $product->getAttribute('price');
                $price = is_numeric($rawPrice) ? (float) $rawPrice : 0.0;

                $result
                    ->withData('product_id', $product->getKey())
                    ->withData('sku', $rawSku ?? '')
                    ->withData('name', self::resolveName($product))
                    ->withData('price', $price);

                return $result;
            })
            ->all();
    }

    /**
     * @return Builder<Product>
     */
    private static function baseQuery(string $term): Builder
    {
        $search = trim($term);

        return Product::query()
            ->select(['id', 'sku', 'barcode', 'name', 'price'])
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('name->lt', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at');
    }

    private static function formatLabel(Product $product): string
    {
        /** @var string|null $rawSku */
        $rawSku = $product->getAttribute('sku');
        $sku = $rawSku ?? '';
        $name = self::resolveName($product);

        return trim(sprintf('[%s] %s', $sku !== '' ? $sku : '—', $name));
    }

    private static function resolveName(Product $product): string
    {
        $rawName = $product->getAttribute('name');

        if (is_array($rawName)) {
            $locale = app()->getLocale();
            $value = $rawName[$locale] ?? reset($rawName);

            return is_string($value) ? $value : '';
        }

        return is_string($rawName) ? $rawName : '';
    }
}
