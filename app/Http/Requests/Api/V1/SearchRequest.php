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
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.SearchQueryData::MAX_PER_PAGE],
            'types' => ['sometimes', 'array'],
            'types.*' => [Rule::in(['product', 'category', 'brand'])],
        ];
    }
}
