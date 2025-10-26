<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

final class DiscountCodeApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Capture the raw discount code entered by the shopper.
            'code' => ['required', 'string', 'max:50'],
            // Allow cart payloads so the discount engine can recompute totals server-side.
            'cart' => ['sometimes', 'array'],
            'cart.subtotal' => ['sometimes', 'numeric', 'min:0'],
            'cart.items' => ['sometimes', 'array'],
            'cart.items.*.product_id' => ['nullable', 'integer', 'min:1'],
            'cart.items.*.variant_id' => ['nullable', 'integer', 'min:1'],
            'cart.items.*.quantity' => ['nullable', 'integer', 'min:0'],
            'cart.items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            // Shipping context is optional but keeps shipping discounts accurate when present.
            'shipping' => ['sometimes', 'array'],
            'shipping.base_amount' => ['sometimes', 'numeric', 'min:0'],
            // Preserve historical behaviour where order level redemption can be referenced.
            'order_id' => ['nullable', 'exists:orders,id'],
        ];
    }
}

