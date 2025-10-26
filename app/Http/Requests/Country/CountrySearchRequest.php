<?php

declare(strict_types=1);

namespace App\Http\Requests\Country;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;

/**
 * CountrySearchRequest
 *
 * Encapsulates validation logic for the lightweight country search endpoint.
 */
final class CountrySearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow only callers permitted to view country data through the policy layer.
        return $this->user()?->can('viewAny', Country::class) ?? true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        // Ensure valid search terms and pagination controls for the autocomplete endpoint.
        return [
            'q' => ['required', 'string', 'min:2', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
