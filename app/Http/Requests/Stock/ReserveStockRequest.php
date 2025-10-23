<?php

declare(strict_types=1);

namespace App\Http\Requests\Stock;

use App\Models\VariantInventory;
use Illuminate\Foundation\Http\FormRequest;

final class ReserveStockRequest extends FormRequest
{
    private int $maxAvailable = 0;

    protected function prepareForValidation(): void
    {
        $stockId = (int) ($this->route('stock') ?? 0);

        if ($stockId > 0) {
            $inventory = VariantInventory::find($stockId);
            $this->maxAvailable = (int) ($inventory?->available_stock ?? 0);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $this->maxAvailable],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

