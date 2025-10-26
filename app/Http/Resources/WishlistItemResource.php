<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * WishlistItemResource is responsible for transforming a wishlist item into
 * a consistent JSON representation while preserving key product context.
 */
final class WishlistItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Provide a normalized structure for wishlist item payloads.
        return [
            'id'              => $this->id,
            'product_id'      => $this->product_id,
            'variant_id'      => $this->variant_id,
            'quantity'        => $this->quantity,
            'notes'           => $this->notes,
            'display_name'    => $this->display_name,
            'current_price'   => $this->current_price,
            'formatted_price' => $this->formatted_current_price,
            'product'         => $this->whenLoaded('product', function () {
                // Include a light-weight snapshot of the related product.
                return [
                    'id'        => $this->product->id,
                    'name'      => $this->product->name,
                    'slug'      => $this->product->slug,
                    'brand'     => $this->product->brand?->only(['id', 'name']),
                    'thumbnail' => $this->product->getFirstMediaUrl('images', 'thumbnail'),
                ];
            }),
            'variant' => $this->whenLoaded('variant', function () {
                // Provide a quick reference to the selected variant when present.
                return [
                    'id'   => $this->variant->id,
                    'sku'  => $this->variant->sku,
                    'name' => $this->variant->name,
                ];
            }),
        ];
    }
}
