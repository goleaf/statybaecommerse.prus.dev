<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class ProductContract
{
    public const CONTRACT = 'product';

    public const VERSION = 'v1';

    public static function schemaPath(): string
    {
        return resource_path('contracts/v1/product.schema.json');
    }

    public static function examplePath(): string
    {
        return resource_path('contracts/v1/examples/product.json');
    }

    public static function forProduct(Product $product, array $meta = []): array
    {
        return self::envelope([
            'item' => self::mapProduct($product),
        ], $meta);
    }

    public static function forCollection(iterable $products, array $meta = []): array
    {
        $paginator = $products instanceof LengthAwarePaginator ? $products : null;
        $items = $paginator?->getCollection() ?? Collection::make($products);

        $data = [
            'items' => $items->map(fn (Product $product): array => self::mapProduct($product))->values()->all(),
        ];

        if ($paginator instanceof LengthAwarePaginator) {
            $data['pagination'] = [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ];
        }

        return self::envelope($data, $meta);
    }

    private static function mapProduct(Product $product): array
    {
        $product->loadMissing(['brand', 'categories', 'media', 'variants']);
        $categoryNames = $product->categories
            ->map(static function ($category): string {
                // Provide a simple category label for lightweight storefront clients.
                return (string) $category->name;
            })
            ->values()
            ->all();

        return [
            'id'                => $product->getKey(),
            'slug'              => (string) $product->slug,
            'name'              => (string) $product->name,
            'sku'               => (string) $product->sku,
            'description'       => $product->description,
            'short_description' => $product->short_description,
            'pricing'           => [
                'amount'      => (float) $product->price,
                'sale_amount' => $product->sale_price !== null ? (float) $product->sale_price : null,
                'currency'    => config('app.currency', 'EUR'),
            ],
            'brand'      => $product->brand?->exists ? (string) $product->brand->name : null,
            'categories' => $categoryNames,
            'media'      => [
                'images' => $product->getMedia('images')->map(static fn ($media): array => [
                    'url'       => $media->getUrl(),
                    'thumbnail' => $media->getUrl('thumb'),
                    'alt'       => (string) $media->getCustomProperty('alt', ''),
                ])->all(),
            ],
            'variants' => $product->variants->map(static fn ($variant): array => [
                'id'             => $variant->getKey(),
                'name'           => (string) $variant->name,
                'sku'            => (string) $variant->sku,
                'price'          => (float) $variant->price,
                'stock_quantity' => $variant->stock_quantity,
            ])->all(),
            'inventory' => [
                'manage_stock'   => (bool) $product->manage_stock,
                'stock_quantity' => $product->stock_quantity,
                'is_in_stock'    => $product->isInStock(),
            ],
            'status' => [
                'is_visible'  => (bool) $product->is_visible,
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
            'version'  => self::VERSION,
            'data'     => $data,
            'meta'     => $meta,
        ];
    }
}
