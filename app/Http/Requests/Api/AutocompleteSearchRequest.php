<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Rules\ModelClassRule;

final class AutocompleteSearchRequest extends ApiRequest
{
    protected ?string $requiredAbility = 'system.autocomplete';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'model_class'  => ['required', 'string', new ModelClassRule],
            'search_field' => ['sometimes', 'string'],
            'search_query' => ['required', 'string'],
            'value_field'  => ['sometimes', 'string'],
            'label_field'  => ['sometimes', 'string'],
            'limit'        => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
