<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

final class OrderUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.product_id'         => ['required', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity'           => ['required', 'integer', 'min:1'],
            'billing_address'            => ['required', 'array'],
            'shipping_address'           => ['required', 'array'],
            'notes'                      => ['nullable', 'string', 'max:1000'],
            'payment_method'             => ['nullable', 'string', 'max:255'],
        ];
    }
}
