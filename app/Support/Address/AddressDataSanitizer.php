<?php

declare(strict_types=1);

namespace App\Support\Address;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * AddressDataSanitizer
 *
 * Helper responsible for canonicalising user supplied address fragments and
 * stripping potentially dangerous characters before the data is persisted.
 */
final class AddressDataSanitizer
{
    /**
     * Clean and normalise the provided address payload.
     *
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public static function sanitize(array $payload, ?string $countryCode = null): array
    {
        // Normalise keys that contain textual input.
        foreach (self::stringFields() as $field) {
            if (! array_key_exists($field, $payload)) {
                continue;
            }

            $payload[$field] = self::cleanString(Arr::get($payload, $field));
        }

        // Canonicalise the country code so that validation can rely on a
        // consistent representation.
        if (array_key_exists('country_code', $payload) && is_string($payload['country_code'])) {
            $payload['country_code'] = strtoupper(trim($payload['country_code']));
        }

        // Postal codes should be compact, uppercase and without stray separators.
        if (array_key_exists('postal_code', $payload)) {
            $payload['postal_code'] = self::normalisePostalCode($payload['postal_code'], $countryCode ?? ($payload['country_code'] ?? null));
        }

        // Email addresses are always stored in lowercase for consistency.
        if (array_key_exists('email', $payload) && is_string($payload['email'])) {
            $payload['email'] = Str::lower(trim($payload['email']));
        }

        // Phone numbers are filtered to keep characters useful for dialling.
        if (array_key_exists('phone', $payload)) {
            $payload['phone'] = self::normalisePhone($payload['phone']);
        }

        // Boolean like checkboxes arrive as string values, so we convert them
        // into strict booleans ahead of persistence.
        foreach (self::booleanFields() as $field) {
            if (! array_key_exists($field, $payload)) {
                continue;
            }

            $payload[$field] = filter_var($payload[$field], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
        }

        // Address types are represented as lowercase strings.
        if (array_key_exists('type', $payload) && is_string($payload['type'])) {
            $payload['type'] = Str::lower(trim($payload['type']));
        }

        return $payload;
    }

    /**
     * List of fields that should pass through the string normaliser.
     *
     * @return array<int, string>
     */
    private static function stringFields(): array
    {
        return [
            'first_name',
            'last_name',
            'company',
            'company_name',
            'company_vat',
            'address_line_1',
            'address_line_2',
            'city',
            'state',
            'apartment',
            'floor',
            'building',
            'landmark',
            'instructions',
            'notes',
        ];
    }

    /**
     * Fields that should be coerced to boolean values.
     *
     * @return array<int, string>
     */
    private static function booleanFields(): array
    {
        return ['is_default', 'is_billing', 'is_shipping', 'is_active'];
    }

    /**
     * Remove risky characters while keeping human readable formatting.
     */
    private static function cleanString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        // Strip tags and control characters.
        $sanitised = preg_replace('/[\x00-\x1F\x7F<>"\'`]/u', '', strip_tags($trimmed));
        if ($sanitised === null) {
            $sanitised = $trimmed;
        }

        // Collapse repeated whitespace so stored values remain clean.
        $sanitised = preg_replace('/\s+/u', ' ', $sanitised) ?? $sanitised;

        return $sanitised === '' ? null : $sanitised;
    }

    /**
     * Canonicalise postal codes using country specific rules.
     */
    private static function normalisePostalCode(mixed $postalCode, mixed $countryCode): ?string
    {
        if (! is_string($postalCode)) {
            return null;
        }

        $compact = strtoupper(str_replace([' ', '-'], '', trim($postalCode)));

        if (! is_string($countryCode) || $countryCode === '') {
            return $compact === '' ? null : $compact;
        }

        $countryCode = strtoupper($countryCode);

        if ($countryCode === 'LT') {
            // Lithuanian postal codes use five digits. Reintroduce the dash if
            // the user provided the official LT-XXXXX formatting.
            if (strlen($compact) === 5) {
                return $compact;
            }

            if (str_starts_with($compact, 'LT') && strlen($compact) === 7) {
                return 'LT-' . substr($compact, -5);
            }
        }

        return $compact === '' ? null : $compact;
    }

    /**
     * Keep dialable characters so we do not store injected scripts.
     */
    private static function normalisePhone(mixed $phone): ?string
    {
        if (! is_string($phone)) {
            return null;
        }

        $filtered = preg_replace('/[^\d+]/', '', $phone) ?? '';

        return $filtered === '' ? null : $filtered;
    }
}
