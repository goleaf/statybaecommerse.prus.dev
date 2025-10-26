<?php

declare(strict_types=1);

namespace App\Http\Requests\Stock;

use App\Models\VariantInventory;
use Illuminate\Foundation\Http\FormRequest;

final class UnreserveStockRequest extends FormRequest
{
    private int $maxReserved = 0;

    protected function prepareForValidation(): void
    {
        // Resolve the bound inventory safely regardless of route parameter type.
        $inventory = $this->resolveInventoryFromRoute();

        // Store the reserved count for later validation rules.
        $this->maxReserved = (int) ($inventory?->reserved ?? 0);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $this->maxReserved],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return VariantInventory|null Resolve the inventory record when the router hands us either a model or ID.
     */
    private function resolveInventoryFromRoute(): ?VariantInventory
    {
        $stock = $this->route('stock');

        // Early-return when the inventory has already been resolved by Laravel's router.
        if ($stock instanceof VariantInventory) {
            return $stock;
        }

        if (! is_numeric($stock)) {
            return null;
        }

        return VariantInventory::find((int) $stock);
    }
}
