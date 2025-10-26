<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DeterministicTotalsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Normalise the scalar inputs so validation and downstream lookups remain predictable.
        $country = strtoupper((string) $this->input('destination.country', ''));
        $region = strtoupper((string) $this->input('destination.region', ''));
        $service = strtolower((string) $this->input('service', ''));

        $this->merge([
            'destination' => [
                'country' => $country,
                'region' => $region,
                'postal_code' => (string) $this->input('destination.postal_code', ''),
            ],
            'service' => $service,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Destination geo data is required to ensure tax/shipping rates are deterministic.
            'destination' => ['required', 'array'],
            'destination.country' => ['required', 'string', 'size:2'],
            'destination.region' => ['required', 'string', 'max:20'],
            'destination.postal_code' => ['required', 'string', 'max:20'],

            // Service identifiers are constrained by configuration to avoid arbitrary rate injection.
            'service' => [
                'required',
                'string',
                Rule::in(array_keys(config('deterministic_totals.services', []))),
            ],

            // Line items feed the subtotal and must contain sane numeric values.
            'items' => ['required', 'array', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],

            // Discounts and client-provided rate echoes are optional but validated when present.
            'discounts' => ['sometimes', 'array'],
            'discounts.amount' => ['sometimes', 'numeric', 'min:0'],

            'client_rates' => ['sometimes', 'array'],
            'client_rates.tax_rate' => ['sometimes', 'numeric', 'min:0'],
            'client_rates.tax_amount' => ['sometimes', 'numeric', 'min:0'],
            'client_rates.shipping_amount' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
