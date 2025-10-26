<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ProductResource
 *
 * Compact product representation tailored for catalogue listings and nested
 * category responses in the storefront API.
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array for JSON responses.
     */
    public function toArray($request): array
    {
        // Provide key merchandising fields so clients can render product cards quickly.
        return [
            'id' => $this->resource->getKey(),
            'slug' => (string) $this->resource->slug,
            'name' => (string) $this->resource->name,
            'price' => $this->resource->price,
            'sale_price' => $this->resource->sale_price,
            'currency' => config('app.currency', 'EUR'),
            'brand' => $this->whenLoaded('brand', function (): array {
                // Expose a small brand payload without triggering additional queries thanks to eager loading.
                return [
                    'id' => $this->brand->getKey(),
                    'name' => (string) $this->brand->name,
                    'slug' => (string) $this->brand->slug,
                ];
            }),
            'primary_image_url' => $this->resource->getFirstMediaUrl('default'),
            'published_at' => optional($this->resource->published_at)?->toISOString(),
            'is_featured' => (bool) $this->resource->is_featured,
        ];
    }
}
