<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ProductResource exposes a stable product representation for external clients.
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var \App\Models\Product $product */
        $product = $this->resource;

        // Build a normalised media payload for gallery rendering.
        $media = $product->relationLoaded('media')
            ? $product->getMedia('images')->map(static fn ($item): array => [
                'url'       => $item->getUrl(),
                'thumbnail' => $item->hasGeneratedConversion('thumb') ? $item->getUrl('thumb') : $item->getUrl(),
                'alt'       => (string) $item->getCustomProperty('alt', ''),
            ])->all()
            : [];

        // Collect categories so callers can build breadcrumbs without extra queries.
        $categories = $product->relationLoaded('categories')
            ? $product->categories->map(static fn ($category): array => [
                'id'   => $category->getKey(),
                'name' => (string) $category->name,
                'slug' => (string) $category->slug,
            ])->all()
            : [];

        return [
            'id'                => $product->getKey(),
            'slug'              => (string) $product->slug,
            'name'              => (string) $product->name,
            'sku'               => (string) $product->sku,
            'description'       => $product->description,
            'short_description' => $product->short_description,
            'pricing'           => [
                'amount'      => $product->price !== null ? (float) $product->price : null,
                'sale_amount' => $product->sale_price !== null ? (float) $product->sale_price : null,
                'currency'    => config('app.currency', 'EUR'),
            ],
            'brand' => $product->relationLoaded('brand') && $product->brand
                ? [
                    'id'   => $product->brand->getKey(),
                    'name' => (string) $product->brand->name,
                    'slug' => (string) $product->brand->slug,
                ]
                : null,
            'categories' => $categories,
            'media'      => [
                'images' => $media,
            ],
            'variants'  => ProductVariantResource::collection(
                $product->relationLoaded('variants') ? $product->variants : collect()
            ),
            'inventory' => [
                'manage_stock'   => (bool) $product->manage_stock,
                'stock_quantity' => (int) ($product->stock_quantity ?? 0),
                'is_in_stock'    => $product->isInStock(),
            ],
            'status' => [
                'is_visible'  => (bool) $product->is_visible,
                'is_featured' => (bool) $product->is_featured,
            ],
            'reviews_count' => $product->reviews_count ?? 0,
            'links'         => [
                'self' => route('product.show', $product->slug),
            ],
        ];
    }
}
