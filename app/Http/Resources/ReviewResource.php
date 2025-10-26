<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ReviewResource
 *
 * JSON resource responsible for shaping review payloads returned to API
 * consumers after a successful submission.
 */
final class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->whenLoaded('product');

        return [
            'id' => $this->resource->getKey(),
            'product_id' => $this->resource->product_id,
            'user_id' => $this->resource->user_id,
            'rating' => $this->resource->rating,
            'title' => $this->resource->title,
            'content' => $this->resource->content,
            'is_approved' => (bool) $this->resource->is_approved,
            'is_verified_purchase' => (bool) $this->resource->is_verified_purchase,
            'metadata' => $this->resource->metadata ?? [],
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
            'product' => $product ? [
                'id' => $product->getKey(),
                'average_rating' => $product->average_rating !== null ? (float) $product->average_rating : null,
                'reviews_count' => $product->reviews_count ?? 0,
            ] : null,
        ];
    }
}
