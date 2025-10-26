<?php

declare(strict_types=1);

namespace App\Data\Storefront\Shared;

use App\Models\CartItem;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Serializable representation of a cart item for the shopping cart widget.
 */
final class CartItemData implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly int $productId,
        public readonly ?int $variantId,
        public readonly string $name,
        public readonly float $unitPrice,
        public readonly int $quantity,
        public readonly float $totalPrice,
        public readonly array $snapshot,
        public readonly ?string $imageUrl,
    ) {
        // DTO ensures Livewire receives sanitized data while avoiding accidental lazy loading in the view.
    }

    public static function fromModel(CartItem $item): self
    {
        $product = $item->product;
        $imageUrl = null;

        if ($product !== null && method_exists($product, 'getFirstMediaUrl')) {
            $imageUrl = $product->getFirstMediaUrl() ?: null;
        }

        return new self(
            (int) $item->id,
            (int) $item->product_id,
            $item->variant_id !== null ? (int) $item->variant_id : null,
            (string) ($item->product_snapshot['name'] ?? $item->product?->name ?? ''),
            (float) $item->unit_price,
            (int) $item->quantity,
            (float) $item->total_price,
            is_array($item->product_snapshot) ? $item->product_snapshot : [],
            $imageUrl,
        );
    }

    /**
     * @return array{id:int, product_id:int, variant_id:int|null, name:string, unit_price:float, quantity:int, total_price:float, snapshot:array<string,mixed>, image_url:?string}
     */
    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'product_id'  => $this->productId,
            'variant_id'  => $this->variantId,
            'name'        => $this->name,
            'unit_price'  => $this->unitPrice,
            'quantity'    => $this->quantity,
            'total_price' => $this->totalPrice,
            'snapshot'    => $this->snapshot,
            'image_url'   => $this->imageUrl,
        ];
    }
}
