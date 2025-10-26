<?php

declare(strict_types=1);

namespace App\Http\Requests\Frontend;

use App\Enums\AddressType;
use App\Support\Address\AddressDataSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * AddressRequest
 *
 * Shared validation rules and sanitisation logic for address creation and
 * updates, ensuring a consistent allow-list approach across the application.
 */
abstract class AddressRequest extends FormRequest
{
    /**
     * Prepare the data for validation by canonicalising user input.
     */
    protected function prepareForValidation(): void
    {
        // Canonicalise incoming payload ahead of validation so all checks run on
        // the cleaned, allow-listed data.
        $this->merge(AddressDataSanitizer::sanitize($this->all(), $this->input('country_code')));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $countryRule = Rule::in($this->allowedCountries());

        $allowedTypes = array_map(static fn (AddressType $type): string => $type->value, AddressType::cases());

        return [
            'type'           => ['required', Rule::in($allowedTypes)],
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'company'        => ['nullable', 'string', 'max:255'],
            'company_name'   => ['nullable', 'string', 'max:255'],
            'company_vat'    => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'apartment'      => ['nullable', 'string', 'max:50'],
            'floor'          => ['nullable', 'string', 'max:50'],
            'building'       => ['nullable', 'string', 'max:100'],
            'city'           => ['required', 'string', 'max:150'],
            'state'          => ['nullable', 'string', Rule::in($this->allowedRegionsForCountry($this->input('country_code')))],
            'postal_code'    => ['required', 'string', 'max:20'],
            'country_code'   => ['required', 'string', 'size:2', $countryRule],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:255'],
            'notes'          => ['nullable', 'string', 'max:500'],
            'instructions'   => ['nullable', 'string', 'max:500'],
            'is_default'     => ['sometimes', 'boolean'],
            'is_billing'     => ['sometimes', 'boolean'],
            'is_shipping'    => ['sometimes', 'boolean'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance with additional allow-list checks.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $country = Str::upper((string) $this->input('country_code'));

            if ($country !== '' && ! in_array($country, $this->allowedCountries(), true)) {
                $validator->errors()->add('country_code', __('The selected country is not supported.'));
            }

            $state = (string) $this->input('state');
            $allowedRegions = $this->allowedRegionsForCountry($country);
            if ($state !== '' && $allowedRegions !== [] && ! in_array($state, $allowedRegions, true)) {
                $validator->errors()->add('state', __('The selected region is not supported for the chosen country.'));
            }

            $postalCode = (string) $this->input('postal_code');
            $pattern = $this->postalCodePattern($country);
            if ($postalCode !== '' && $pattern !== null && preg_match($pattern, $postalCode) !== 1) {
                $validator->errors()->add('postal_code', __('The postal code format is invalid for the selected country.'));
            }
        });
    }

    /**
     * Retrieve the allow-listed countries.
     *
     * @return array<int, string>
     */
    protected function allowedCountries(): array
    {
        $configured = config('addresses.allowed_countries', []);

        return $configured === [] ? $this->fallbackCountries() : $configured;
    }

    /**
     * Provide a fallback set of countries based on active entries in storage.
     *
     * @return array<int, string>
     */
    private function fallbackCountries(): array
    {
        return (array) DB::table('countries')
            ->where('is_active', true)
            ->pluck('cca2')
            ->map(static fn (string $code): string => Str::upper($code))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Determine the allow-listed regions for the provided country.
     *
     * @return array<int, string>
     */
    protected function allowedRegionsForCountry(?string $country): array
    {
        if ($country === null) {
            return [];
        }

        $country = Str::upper($country);

        return config("addresses.allowed_regions.$country", []);
    }

    /**
     * Fetch the country specific postal code pattern if configured.
     */
    protected function postalCodePattern(?string $country): ?string
    {
        if ($country === null) {
            return null;
        }

        $country = Str::upper($country);

        return config("addresses.postal_code_patterns.$country");
    }

    /**
     * Override validated() to ensure the returned data remains sanitised.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        return AddressDataSanitizer::sanitize($validated, Arr::get($validated, 'country_code'));
    }
}
