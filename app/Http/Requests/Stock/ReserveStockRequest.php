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
        // Resolve the inventory instance from either implicit model binding or a raw identifier.
        $inventory = $this->resolveInventoryFromRoute();

        // Cache the available stock count for later validation without additional queries.
        $this->maxAvailable = (int) ($inventory?->available_stock ?? 0);
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

    /**
     * @return VariantInventory|null Resolve the bound inventory even if only an ID was provided.
     */
    private function resolveInventoryFromRoute(): ?VariantInventory
    {
        $stock = $this->route('stock');

        // If the router already provided a model instance we can use it directly.
        if ($stock instanceof VariantInventory) {
            return $stock;
        }

        // Guard against non-numeric values before attempting a lookup.
        if (! is_numeric($stock)) {
            return null;
        }

        return VariantInventory::find((int) $stock);
    }
}

