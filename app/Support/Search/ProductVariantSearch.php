<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Filament\SearchableComponentHelper;
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
        $variants = self::baseQuery($term)
            ->limit($limit)
            ->get();

        return $variants
            ->map(static fn (ProductVariant $variant): SearchResult => self::toResult($variant))
            ->all();
    }

    public static function label(ProductVariant $variant): string
    {
        $sku = self::stringValue($variant->getAttribute('sku'));
        $name = self::stringValue($variant->getAttribute('name'));
        $product = $variant->getRelationValue('product');
        $productName = $product instanceof Product ? self::resolveProductName($product) : '';

        $skuPart = $sku !== '' ? sprintf('[%s]', $sku) : '[—]';
        $variantPart = $name !== '' ? $name : __('products.labels.unnamed_variant');

        return trim(sprintf('%s %s%s', $skuPart, $productName !== '' ? "{$productName} — " : '', $variantPart));
    }

    /**
     * @return array<string, mixed>
     */
    public static function payload(ProductVariant $variant): array
    {
        $product = $variant->getRelationValue('product');

        return [
            'variant_id'   => $variant->getKey(),
            'product_id'   => $variant->getAttribute('product_id'),
            'sku'          => self::stringValue($variant->getAttribute('sku')),
            'name'         => self::stringValue($variant->getAttribute('name')),
            'price'        => self::numericValue($variant->getAttribute('price')),
            'product_sku'  => $product instanceof Product ? self::stringValue($product->getAttribute('sku')) : '',
            'product_name' => $product instanceof Product ? self::resolveProductName($product) : '',
        ];
    }

    public static function hydrateComponent(SearchableInput $component, ?int $state): void
    {
        SearchableComponentHelper::hydrate(
            $component,
            $state,
            static function (int $identifier): ?ProductVariant {
                return ProductVariant::query()
                    ->select(['id', 'product_id', 'sku', 'name', 'price'])
                    ->with(['product:id,sku,name'])
                    ->find($identifier);
            },
            static function (ProductVariant $variant): array {
                $result = self::toResult($variant);
                $payload = SearchResultPayload::hydrate($result)['payload'];

                return [
                    'value'   => $result->value(),
                    'label'   => $result->label(),
                    'payload' => $payload,
                ];
            },
        );
    }

    private static function baseQuery(string $term): Builder
    {
        $search = trim($term);

        return ProductVariant::query()
            ->select(['id', 'product_id', 'name', 'sku', 'price', 'updated_at'])
            ->with(['product:id,name,sku'])
            ->when($search !== '', static function (Builder $builder) use ($search): void {
                $builder->where(static function (Builder $query) use ($search): void {
                    $like = "%{$search}%";

                    $query
                        ->where('name', 'like', $like)
                        ->orWhere('sku', 'like', $like)
                        ->orWhereHas('product', static function (Builder $productQuery) use ($like): void {
                            $productQuery
                                ->where('name', 'like', $like)
                                ->orWhere('sku', 'like', $like);
                        });
                });
            })
            ->orderByDesc('updated_at');
    }

    private static function toResult(ProductVariant $variant): SearchResult
    {
        $identifier = (string) ($variant->getKey() ?? '');
        $result = SearchResult::make($identifier, self::label($variant));

        return SearchResultPayload::normalise($result, self::payload($variant));
    }

    private static function resolveProductName(Product $product): string
    {
        $rawName = $product->getAttribute('name');

        if (is_array($rawName)) {
            $locale = app()->getLocale();
            $value = $rawName[$locale] ?? reset($rawName);

            return is_string($value) ? $value : '';
        }

        return is_string($rawName) ? $rawName : '';
    }

    private static function numericValue(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private static function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
