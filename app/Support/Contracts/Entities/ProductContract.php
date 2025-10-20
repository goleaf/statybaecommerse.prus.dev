<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use function collect;

final class ProductContract
{
    public static function fromModel(Product $product): array
    {
        $product->loadMissing(['brand', 'categories', 'media']);

        $payload = [
            'id' => (int) $product->getKey(),
            'sku' => (string) $product->sku,
            'title' => (string) ($product->name ?? ''),
            'description' => (string) ($product->description ?? ''),
            'price' => (float) $product->price,
            'currency' => strtoupper((string) ($product->currency ?? config('shared.default_currency', config('app.currency', 'EUR')))),
            'stock' => max(0, (int) ($product->stock_quantity ?? 0)),
            'media' => $product->getMedia('images')->map(function ($media): array {
                return [
                    'type' => 'image',
                    'url' => $media->getFullUrl(),
                    'alt' => $media->getCustomProperty('alt', ''),
                    'primary' => (bool) $media->getCustomProperty('primary', false),
                    'variants' => collect($media->generated_conversions ?? [])->mapWithKeys(
                        function (bool $enabled, string $conversion) use ($media): array {
                            if (! $enabled) {
                                return [];
                            }

                            return [$conversion => $media->getFullUrl($conversion)];
                        }
                    )->toArray(),
                ];
            })->values()->toArray(),
            'categories' => self::mapCategories($product->categories),
            'attributes' => self::extractAttributes($product),
            'status' => self::mapStatus($product->status, $product->is_visible),
            'slug' => (string) ($product->slug ?? ''),
        ];

        if ($product->brand) {
            $payload['brand'] = BrandContract::fromModel($product->brand);
        }

        return $payload;
    }

    /**
     * @return array<int, array{id:int,slug:string,title:string}>
     */
    private static function mapCategories(Collection $categories): array
    {
        return $categories->map(function (Category $category): array {
            return [
                'id' => (int) $category->getKey(),
                'slug' => (string) $category->slug,
                'title' => (string) ($category->name ?? ''),
            ];
        })->values()->toArray();
    }

    private static function extractAttributes(Product $product): array
    {
        $attributes = [];
        if (is_array($product->metadata) && $product->metadata !== []) {
            $attributes = $product->metadata;
        }

        if ($product->relationLoaded('attributes')) {
            foreach ($product->attributes as $attribute) {
                $attributes[$attribute->slug ?? $attribute->name] = $attribute->pivot?->attribute_value_id;
            }
        }

        return $attributes;
    }

    private static function mapStatus(?string $status, bool $isVisible): string
    {
        return match ($status) {
            'active', 'inactive', 'archived', 'draft' => $status,
            default => $isVisible ? 'active' : 'inactive',
        };
    }
}
