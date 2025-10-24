<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Models\Product;
use App\Support\Contracts\ContractPathResolver;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class ProductContract
{
    public const CONTRACT = 'product';

    public const VERSION = 'v1';

    public static function schemaPath(): string
    {
        return ContractPathResolver::schema('product.schema.json');
    }

    public static function examplePath(): string
    {
        return ContractPathResolver::example('product.json');
    }

    public static function forProduct(Product $product, array $meta = []): array
    {
        $productPayload = self::mapProduct($product);

        return self::envelope([
            'product' => $productPayload,
            'item' => $productPayload,
        ], $meta);
    }

    public static function forCollection(iterable $products, array $meta = []): array
    {
        $paginator = $products instanceof LengthAwarePaginator ? $products : null;
        $items = $paginator?->getCollection() ?? Collection::make($products);

        $mapped = $items
            ->map(fn (Product $product): array => self::mapProduct($product))
            ->values()
            ->all();

        $data = [
            'products' => $mapped,
            'items' => $mapped,
        ];

        if ($paginator instanceof LengthAwarePaginator) {
            $data['pagination'] = [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ];
        }

        return self::envelope($data, $meta);
    }

    private static function mapProduct(Product $product): array
    {
        $product->loadMissing(['brand', 'categories', 'media', 'variants']);
        $primaryCategory = $product->categories->first();

        return [
            'id' => $product->getKey(),
            'slug' => (string) $product->slug,
            'name' => (string) $product->name,
            'sku' => (string) $product->sku,
            'description' => $product->description,
            'short_description' => $product->short_description,
            'pricing' => [
                'amount' => (float) $product->price,
                'sale_amount' => $product->sale_price !== null ? (float) $product->sale_price : null,
                'currency' => config('app.currency', 'EUR'),
            ],
            'brand' => $product->brand?->exists ? [
                'id' => $product->brand->getKey(),
                'name' => (string) $product->brand->name,
                'slug' => (string) $product->brand->slug,
            ] : null,
            'category' => $primaryCategory ? [
                'id' => $primaryCategory->getKey(),
                'name' => (string) $primaryCategory->name,
                'slug' => (string) $primaryCategory->slug,
            ] : null,
            'media' => [
                'images' => $product->getMedia('images')->map(static fn ($media): array => [
                    'url' => $media->getUrl(),
                    'thumbnail' => $media->getUrl('thumb'),
                    'alt' => (string) $media->getCustomProperty('alt', ''),
                ])->all(),
            ],
            'variants' => $product->variants->map(static fn ($variant): array => [
                'id' => $variant->getKey(),
                'name' => (string) $variant->name,
                'sku' => (string) $variant->sku,
                'price' => (float) $variant->price,
                'stock_quantity' => $variant->stock_quantity,
            ])->all(),
            'inventory' => [
                'manage_stock' => (bool) $product->manage_stock,
                'stock_quantity' => $product->stock_quantity,
                'is_in_stock' => $product->isInStock(),
            ],
            'status' => [
                'is_visible' => (bool) $product->is_visible,
                'is_featured' => (bool) $product->is_featured,
            ],
            'links' => [
                'self' => route('product.show', $product->slug),
            ],
        ];
    }

    private static function envelope(array $data, array $meta = []): array
    {
        $meta = array_merge([
            'generated_at' => now()->toISOString(),
        ], Arr::whereNotNull($meta));

        return [
            'contract' => self::CONTRACT,
            'version' => self::VERSION,
            'data' => $data,
            'meta' => $meta,
        ];
    }
}
