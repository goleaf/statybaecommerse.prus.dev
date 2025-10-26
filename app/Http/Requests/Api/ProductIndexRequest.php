<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\Api\ProductSort;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * ProductIndexRequest centralises filter validation for the public product listing endpoint.
 */
final class ProductIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // The catalogue listing is public so every caller may access it.
        return true;
    }

    /**
     * Sanitize incoming payload before validation so we never persist unsafe filters.
     */
    protected function prepareForValidation(): void
    {
        // Trim and collapse the search term while stripping control characters.
        if ($this->has('q')) {
            $query = Str::of((string) $this->input('q'))
                ->replaceMatches('/[\x00-\x1F\x7F]+/u', '')
                ->stripTags()
                ->squish();

            $this->merge(['q' => $query->value()]);
        }

        // Normalise pagination input by coercing to integers and capping by policy.
        if ($this->has('per_page')) {
            $perPage = (int) $this->input('per_page');
            $this->merge(['per_page' => min(max($perPage, 1), 50)]);
        }

        if ($this->has('price_min')) {
            $this->merge(['price_min' => $this->normaliseDecimal($this->input('price_min'))]);
        }

        if ($this->has('price_max')) {
            $this->merge(['price_max' => $this->normaliseDecimal($this->input('price_max'))]);
        }
    }

    /**
     * Define the validation rules for the request.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|string>>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0', 'gte:price_min'],
            'sort' => ['nullable', Rule::enum(ProductSort::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Provide default filter values so controllers can rely on the validated payload.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null)
    {
        /** @var array<string, mixed> $validated */
        $validated = parent::validated($key, $default);

        return array_merge([
            'q' => null,
            'category' => null,
            'price_min' => null,
            'price_max' => null,
            'sort' => ProductSort::NAME_ASC->value,
            'per_page' => 20,
            'page' => 1,
        ], $validated);
    }

    /**
     * Convert raw numeric filters to decimal strings understood by the validator.
     */
    private function normaliseDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Replace commas with dots so European decimal separators work seamlessly.
        $normalised = str_replace(',', '.', (string) $value);

        return is_numeric($normalised) ? $normalised : null;
    }
}
