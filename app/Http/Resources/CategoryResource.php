<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CategoryResource
 *
 * API resource responsible for serialising category catalogue payloads with
 * nested child categories and associated products for storefront responses.
 */
class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array for JSON responses.
     */
    public function toArray($request): array
    {
        // Provide a predictable structure for category pages including children and product listings.
        return [
            'id' => $this->resource->getKey(),
            'slug' => (string) $this->resource->slug,
            'name' => (string) $this->resource->name,
            'description' => $this->resource->description,
            'short_description' => $this->resource->short_description,
            'parent' => $this->whenLoaded('parent', function (): array {
                // Emit a compact parent descriptor when available so clients can build breadcrumbs easily.
                return [
                    'id' => $this->parent->getKey(),
                    'slug' => (string) $this->parent->slug,
                    'name' => (string) $this->parent->name,
                ];
            }),
            'children' => self::collection($this->whenLoaded('children')),
            'children_pagination' => $this->when(
                $this->relationLoaded('childrenPagination'),
                fn (): array => $this->paginationPayload($this->getRelation('childrenPagination'))
            ),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'products_pagination' => $this->when(
                $this->relationLoaded('productsPagination'),
                fn (): array => $this->paginationPayload($this->getRelation('productsPagination'))
            ),
            'featured_products' => ProductResource::collection($this->whenLoaded('featuredProducts')),
            'links' => [
                'self' => route('api.categories.show', $this->resource->slug),
            ],
        ];
    }

    /**
     * Normalise pagination data from Laravel paginators into a simple array.
     */
    private function paginationPayload(LengthAwarePaginator $paginator): array
    {
        // Align with the pagination metadata emitted elsewhere in the API layer.
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
