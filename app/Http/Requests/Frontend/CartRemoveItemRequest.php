<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

final class CartRemoveItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // JSON consumers may rely on the bound cart item ID, so the product reference is optional.
        if ($this->expectsJson()) {
            return [
                'product_id' => ['sometimes', 'integer', 'exists:products,id'],
            ];
        }

        // Form-based flows require an explicit product identifier to remove the entry.
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ];
    }
}
