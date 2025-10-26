<?php

declare(strict_types=1);

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

final class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer'],
            'reason'   => ['required', 'string', 'in:sale,return,adjustment,manual_adjustment,restock,damage,theft,transfer'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ];
    }
}
