<?php

declare(strict_types=1);

namespace App\Data\Orders;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class CreateOrderData extends Data
{
    public function __construct(
        #[Required]
        public int $customerId,

        #[Required]
        public int $shippingAddressId,

        #[Required]
        public int $billingAddressId,

        #[Required]
        public int $shippingOptionId,

        #[Required]
        public int $paymentMethodId,

        #[Required]
        public string $paymentMethod,

        #[Required]
        public float $subtotal,

        /** @var DataCollection<int, OrderItemData> */
        #[Required]
        public DataCollection $items,

        public ?float $discountTotal = null,
        public ?float $shippingTotal = null,
        public ?string $currencyCode = null,
        public ?string $notes = null,
    ) {}

    public static function rules(): array
    {
        return [
            'customerId'        => ['required', 'integer', 'exists:users,id'],
            'shippingAddressId' => ['required', 'integer', 'exists:addresses,id'],
            'billingAddressId'  => ['required', 'integer', 'exists:addresses,id'],
            'shippingOptionId'  => ['required', 'integer', 'exists:shipping_options,id'],
            'paymentMethodId'   => ['required', 'integer'],
            'paymentMethod'     => ['required', 'string', 'max:100'],
            'subtotal'          => ['required', 'numeric', 'min:0'],
            'items'             => ['required', 'array', 'min:1'],
            'discountTotal'     => ['nullable', 'numeric', 'min:0'],
            'shippingTotal'     => ['nullable', 'numeric', 'min:0'],
            'currencyCode'      => ['nullable', 'string', 'size:3'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ];
    }
}
