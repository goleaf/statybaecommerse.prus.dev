<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\UserWishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * WishlistResource assembles wishlist level information along with a
 * paginated collection of wishlist items to power API responses.
 */
final class WishlistResource extends JsonResource
{
    /**
     * @var LengthAwarePaginator<\App\Models\WishlistItem>
     */
    private LengthAwarePaginator $items;

    public function __construct(UserWishlist $resource, LengthAwarePaginator $items)
    {
        // Store the paginator so that item data and metadata can be returned together.
        parent::__construct($resource);
        $this->items = $items;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Shape the wishlist attributes and include the paginated items payload.
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'is_public'   => $this->is_public,
            'is_default'  => $this->is_default,
            'items'       => WishlistItemResource::collection($this->items->items()),
        ];
    }

    /**
     * Provide pagination metadata and navigational links alongside the payload.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function with($request): array
    {
        // Surface pagination details so clients can render paginated UIs easily.
        return [
            'meta' => [
                'items_pagination' => [
                    'current_page' => $this->items->currentPage(),
                    'last_page'    => $this->items->lastPage(),
                    'per_page'     => $this->items->perPage(),
                    'total'        => $this->items->total(),
                ],
            ],
            'links' => [
                'items' => [
                    'first' => $this->items->url(1),
                    'last'  => $this->items->url($this->items->lastPage()),
                    'prev'  => $this->items->previousPageUrl(),
                    'next'  => $this->items->nextPageUrl(),
                ],
            ],
        ];
    }
}
