<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Product;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Schema;

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

    public static function label(Product $product): string
    {
        return self::formatLabel($product);
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

                // Attach the full metadata payload so Livewire and PHP callbacks share the same structure.
                return SearchResultPayload::normalise($result, [
                    'product_id' => $product->getKey(),
                    'sku'        => $rawSku ?? '',
                    'name'       => self::resolveName($product),
                    'price'      => $price,
                ]);
            })
            ->all();
    }

    /**
     * @return Builder<Product>
     */
    private static function baseQuery(string $term): Builder
    {
        $search = trim($term);

        $builder = Product::query()
            ->withoutGlobalScope(SoftDeletingScope::class)
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

        if (Schema::hasColumn($builder->getModel()->getTable(), 'deleted_at')) {
            // Respect soft delete semantics when the column exists so storefront queries stay aligned with production data.
            $builder->whereNull('deleted_at');
        }

        return $builder;
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
