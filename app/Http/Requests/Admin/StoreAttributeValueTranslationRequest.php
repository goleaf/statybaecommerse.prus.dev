<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreAttributeValueTranslationRequest
 *
 * Form request handling validation for creating attribute value translations.
 */
final class StoreAttributeValueTranslationRequest extends FormRequest
{
    /**
     * Handle authorize functionality with proper error handling.
     */
    public function authorize(): bool
    {
        // Controllers are protected by the auth middleware, so we can allow the request to continue here.
        return true;
    }

    /**
     * Handle rules functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Resolve supported locales from configuration and keep a sensible default fallback list.
        $supportedLocales = config('app.supported_locales', ['lt', 'en']);
        $supportedLocales = is_array($supportedLocales)
            ? array_values(array_unique(array_map('strval', $supportedLocales)))
            : ['lt', 'en'];

        $attributeValueParam = $this->route('attributeValue');
        $attributeValueId = is_object($attributeValueParam)
            ? (int) $attributeValueParam->getKey()
            : (int) $attributeValueParam;

        return [
            'locale' => [
                'required',
                'string',
                'max:10',
                Rule::in($supportedLocales),
                Rule::unique('attribute_value_translations', 'locale')
                    ->where(fn ($query) => $query->where('attribute_value_id', $attributeValueId)),
            ],
            'value'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'meta_data'   => ['nullable', 'array'],
        ];
    }
}
