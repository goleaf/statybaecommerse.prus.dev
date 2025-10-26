<?php

declare(strict_types=1);

namespace App\Http\Requests\Country;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;

/**
 * CountryIndexRequest
 *
 * Dedicated form request handling validation for the country index endpoint.
 */
final class CountryIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow any caller permitted by the policy to view the list of countries.
        return $this->user()?->can('viewAny', Country::class) ?? true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        // Enforce strict validation on filter, sort and pagination parameters.
        return [
            'region'       => ['nullable', 'string', 'max:255'],
            'currency'     => ['nullable', 'string', 'max:3'],
            'is_eu_member' => ['nullable', 'boolean'],
            'requires_vat' => ['nullable', 'boolean'],
            'search'       => ['nullable', 'string', 'max:255'],
            'sort'         => ['nullable', 'string', 'in:name,-name,sort_order,-sort_order'],
            'per_page'     => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
