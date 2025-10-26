<?php

declare(strict_types=1);

namespace App\Data\Cart;

/**
 * CartLineItemData
 *
 * Snapshot of cart item details sent to Livewire views.
 */
final class CartLineItemData
{
    /**
     * Create a new cart line representation.
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $quantity,
        public readonly float $unitPrice,
        public readonly float $totalPrice,
        public readonly ?string $thumbnailUrl = null,
    ) {
        // Storing pre-formatted metadata keeps rendering trivial.
    }

    /**
     * Convert the data object into a serialisable array.
     *
     * @return array{id:int,name:string,quantity:int,unitPrice:float,totalPrice:float,thumbnailUrl:?string}
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'quantity'     => $this->quantity,
            'unitPrice'    => $this->unitPrice,
            'totalPrice'   => $this->totalPrice,
            'thumbnailUrl' => $this->thumbnailUrl,
        ];
    }
}
