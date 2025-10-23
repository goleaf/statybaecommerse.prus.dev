<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Data\SearchQueryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $query = $this->input('query', $this->input('q'));

        if ($query !== null) {
            $this->merge([
                'query' => $query,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:2', 'max:200'],
            'page'  => ['sometimes', 'integer', 'min:1'],
            // We intentionally skip an upper bound here so that callers exceeding the
            // advertised maximum still receive a capped response instead of a 422
            // validation error. SearchQueryData::fromArray() enforces MAX_PER_PAGE
            // at the domain level which keeps behaviour consistent with legacy
            // integrations that relied on soft capping.
            'per_page' => ['sometimes', 'integer', 'min:1'],
            'types'    => ['sometimes', 'array'],
            'types.*'  => [Rule::in(['product', 'category', 'brand'])],
        ];
    }
}
