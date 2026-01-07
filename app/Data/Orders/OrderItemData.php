<?php

declare(strict_types=1);

namespace App\Data\Orders;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class OrderItemData extends Data
{
    public function __construct(
        #[Required]
        public int $productId,

        #[Required]
        public int $quantity,

        #[Required]
        public float $unitPrice,

        #[Required]
        public string $name,

        #[Required]
        public string $sku,

        public ?string $productType = null,
    ) {}

    public static function rules(): array
    {
        return [
            'productId'   => ['required', 'integer', 'exists:products,id'],
            'quantity'    => ['required', 'integer', 'min:1'],
            'unitPrice'   => ['required', 'numeric', 'min:0'],
            'name'        => ['required', 'string', 'max:255'],
            'sku'         => ['required', 'string', 'max:100'],
            'productType' => ['nullable', 'string', 'max:255'],
        ];
    }
}
