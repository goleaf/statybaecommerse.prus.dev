<?php

declare(strict_types=1);

namespace App\Http\Requests\Country;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;

/**
 * CountryApiRequest
 *
 * Validates public API access to the searchable list of countries.
 */
final class CountryApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Delegate authorization to the dedicated policy, defaulting to public access when unset.
        return $this->user()?->can('viewAny', Country::class) ?? true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        // Guard the API search parameters to avoid unrestricted filtering or pagination.
        return [
            'search'   => ['nullable', 'string', 'max:255'],
            'region'   => ['nullable', 'string', 'max:255'],
            'sort'     => ['nullable', 'string', 'in:name,-name,sort_order,-sort_order'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
