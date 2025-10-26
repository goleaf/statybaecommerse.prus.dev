<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

final class CartUpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Tailor validation to JSON requests that target a single cart line item.
        if ($this->expectsJson()) {
            return [
                'quantity' => ['required', 'integer', 'min:1'],
            ];
        }

        // Provide validation for bulk updates triggered via traditional form submissions.
        return [
            'items'              => ['required_without:product_id', 'array'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'product_id'         => ['sometimes', 'integer', 'exists:products,id'],
            'quantity'           => ['required_without:items', 'integer', 'min:1'],
        ];
    }

    /**
     * Normalise the update payload into a consistent set of product adjustments.
     *
     * @return list<array{product_id:int, quantity:int}>
     */
    public function validatedItems(): array
    {
        if ($this->expectsJson()) {
            return [];
        }

        $validated = $this->validated();

        if (isset($validated['items'])) {
            return array_map(static function (array $item): array {
                return [
                    'product_id' => (int) $item['product_id'],
                    'quantity'   => (int) $item['quantity'],
                ];
            }, $validated['items']);
        }

        if (isset($validated['product_id'], $validated['quantity'])) {
            return [[
                'product_id' => (int) $validated['product_id'],
                'quantity'   => (int) $validated['quantity'],
            ]];
        }

        return [];
    }
}
