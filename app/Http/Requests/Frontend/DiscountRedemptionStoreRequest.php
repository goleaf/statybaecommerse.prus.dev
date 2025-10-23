<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

final class DiscountRedemptionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'discount_code' => ['required', 'string', 'exists:discount_codes,code'],
            'order_id' => ['nullable', 'exists:orders,id'],
        ];
    }
}

