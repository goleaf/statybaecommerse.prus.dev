<?php

declare(strict_types=1);

namespace App\Data\Shipping;

/**
 * ShippingOptionData
 *
 * View model describing a single shipping option in checkout flows.
 */
final class ShippingOptionData
{
    /**
     * Create a new shipping option representation.
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly float $price,
        public readonly string $formattedPrice,
        public readonly string $estimatedDelivery,
    ) {
        // Keep checkout data predictable for Livewire hydration.
    }

    /**
     * Export the data object to a scalar array for frontend usage.
     *
     * @return array{id:int,name:string,price:float,formatted_price:string,estimated_delivery:string}
     */
    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'price'              => $this->price,
            'formatted_price'    => $this->formattedPrice,
            'estimated_delivery' => $this->estimatedDelivery,
        ];
    }
}
