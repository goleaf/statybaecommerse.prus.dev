<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ApplyCouponRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code'                    => ['required', 'string', 'max:50'],
            'cart'                    => ['sometimes', 'array'],
            'cart.subtotal'           => ['sometimes', 'numeric', 'min:0'],
            'cart.items'              => ['sometimes', 'array'],
            'cart.items.*.product_id' => ['nullable', 'integer', 'min:1'],
            'cart.items.*.variant_id' => ['nullable', 'integer', 'min:1'],
            'cart.items.*.quantity'   => ['nullable', 'integer', 'min:0'],
            'cart.items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'shipping'                => ['sometimes', 'array'],
            'shipping.base_amount'    => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
