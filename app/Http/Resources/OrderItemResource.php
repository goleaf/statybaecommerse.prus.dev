<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OrderItemResource
 *
 * API resource that normalizes order item attributes for checkout responses.
 *
 * @mixin OrderItem
 */
final class OrderItemResource extends JsonResource
{
    /**
     * Transform the order item into a consistent JSON structure.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->resource->getKey(),
            'product_id'         => $this->resource->product_id,
            'product_variant_id' => $this->resource->product_variant_id,
            'sku'                => $this->resource->sku,
            'name'               => $this->resource->name,
            'quantity'           => (int) $this->resource->quantity,
            'unit_price'         => (float) $this->resource->unit_price,
            'total'              => (float) $this->resource->total,
        ];
    }
}
