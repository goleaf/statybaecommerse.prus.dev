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
            // Require a minimum length so expensive empty/fuzzy lookups are avoided up front.
            'query'    => ['required', 'string', 'min:3', 'max:200'],
            'page'     => ['sometimes', 'integer', 'min:1'],
            // Cap the requested page size at the DTO maximum to avoid unbounded scans.
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:' . SearchQueryData::MAX_PER_PAGE],
            // Filters and sort inputs are optional but must match the allow-list handled server side.
            'filters'  => ['sometimes', 'array'],
            'filters.*'=> ['nullable'],
            'sort'     => ['sometimes', 'string', Rule::in(SearchQueryData::ALLOWED_SORTS)],
            // Aggregation buckets are managed server-side – ignore any client provided values.
            'types'    => ['prohibited'],
        ];
    }
}
