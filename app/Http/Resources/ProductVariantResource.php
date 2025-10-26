<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ProductVariantResource formats product variant data for API consumers.
 */
class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var \App\Models\ProductVariant $variant */
        $variant = $this->resource;

        return [
            'id' => $variant->getKey(),
            'name' => (string) $variant->name,
            'sku' => (string) $variant->sku,
            'price' => $variant->price !== null ? (float) $variant->price : null,
            'compare_price' => $variant->compare_price !== null ? (float) $variant->compare_price : null,
            'is_default' => (bool) $variant->is_default,
            'stock_quantity' => (int) ($variant->stock_quantity ?? 0),
            'is_in_stock' => ! $variant->isOutOfStock(),
        ];
    }
}
