<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Legal;
use App\Models\Translations\LegalTranslation;

use function array_filter;
use function array_map;
use function array_unique;
use function ctype_digit;
use function explode;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function report;
use function strtolower;

use Throwable;

use function trim;

/**
 * LegalTranslationController
 *
 * HTTP controller handling LegalTranslationController related web requests, responses, and business logic with proper validation
 * and error handling.
 */
final class LegalTranslationController extends Controller
{
    /**
     * Persist a translated copy of a legal document for the requested locale.
     */
    public function update(Request $request, string $locale, int $legalId, string $lang): RedirectResponse
    {
        // Normalize the requested translation locale to ensure consistent lookups in the database layer.
        $targetLocale = strtolower(trim($lang));
        if ($targetLocale === '') {
            abort(404, 'Translation locale is required.');
        }

        // Build a whitelist of supported locales from configuration to avoid storing arbitrary locales from user input.
        $supportedLocales = $this->normalizeSupportedLocales(Config::get('app.supported_locales', ['lt', 'en']));
        if (! in_array($targetLocale, $supportedLocales, true)) {
            abort(404, 'Unsupported translation locale.');
        }

        // Load the underlying legal document even when global scopes would normally hide it from admin queries.
        $legal = Legal::withoutGlobalScopes()->with('translations')->findOrFail($legalId);

        // Grab the existing translation (if any) so we can prepare uniqueness checks for slug handling.
        $existingTranslation = $legal->translations->firstWhere('locale', $targetLocale);

        try {
            // Validate the translation payload to prevent invalid or excessively long content from being persisted.
            /**
             * @var array{
             *     title?: string|null,
             *     slug?: string|null,
             *     content?: string|null,
             *     seo_title?: string|null,
             *     seo_description?: string|null,
             *     meta_data?: array<array-key, mixed>|null
             * } $validated
             */
            $validated = $request->validate([
                'title'           => ['nullable', 'string', 'max:255'],
                'slug'            => ['nullable', 'string', 'max:255'],
                'content'         => ['nullable', 'string'],
                'seo_title'       => ['nullable', 'string', 'max:255'],
                'seo_description' => ['nullable', 'string', 'max:512'],
                'meta_data'       => ['nullable', 'array'],
            ]);
        } catch (ValidationException $exception) {
            // Surface validation errors as a redirect response to keep parity with Laravel's default behaviour for admin forms.
            return back()->withInput()->withErrors($exception->errors());
        }

        // Ensure the meta data payload is a clean array by stripping out null/empty values and non-array submissions.
        $metaData = $validated['meta_data'] ?? null;
        if (! is_array($metaData)) {
            $metaData = [];
        }
        $validated['meta_data'] = array_filter(
            $metaData,
            static fn ($value): bool => $value !== null && $value !== ''
        );

        // Generate a slug when one is not provided, falling back to the document key for predictable URLs.
        $requestedSlugSource = $validated['slug'] ?? null;
        $requestedSlug = is_string($requestedSlugSource) ? trim($requestedSlugSource) : '';
        if ($requestedSlug === '') {
            $sourceForSlug = $validated['title'] ?? null;
            if (! is_string($sourceForSlug) || trim($sourceForSlug) === '') {
                $existingTitle = $existingTranslation?->getAttribute('title');
                $sourceForSlug = is_string($existingTitle) && trim($existingTitle) !== '' ? $existingTitle : $legal->key;
            }

            $requestedSlug = Str::slug($sourceForSlug);
        }
        if ($requestedSlug === '') {
            $requestedSlug = Str::slug($legal->key . '-' . $targetLocale);
        }
        if ($requestedSlug === '') {
            $requestedSlug = strtolower($legal->key . '-' . $targetLocale);
        }

        // Guarantee slug uniqueness by appending an incremental suffix when collisions exist.
        $existingTranslationId = $existingTranslation?->getKey();
        $ignoreId = null;
        if (is_int($existingTranslationId)) {
            $ignoreId = $existingTranslationId;
        } elseif (is_string($existingTranslationId) && ctype_digit($existingTranslationId)) {
            $ignoreId = (int) $existingTranslationId;
        }

        $validated['slug'] = $this->ensureUniqueSlug($requestedSlug, $ignoreId);

        try {
            // Delegate the persistence logic to the model helper so translations stay encapsulated within the aggregate root.
            /** @var array<string, mixed> $payload */
            $payload = $validated;
            $legal->updateTranslation($targetLocale, $payload);
        } catch (Throwable $throwable) {
            // Log the exception and redirect back with a human-readable error to aid support investigations.
            report($throwable);

            return back()->withInput()->withErrors([
                'translation' => __('Unable to save legal translation.'),
            ]);
        }

        // Provide user feedback in the UI to confirm the translation was stored successfully.
        return back()->with('status', __('Legal translation updated successfully.'));
    }

    /**
     * Normalize configured locales into a trimmed, lowercase array for reliable comparisons.
     *
     * @return array<int, string>
     */
    private function normalizeSupportedLocales(mixed $locales): array
    {
        // Convert comma-separated strings to arrays so downstream logic only works with list structures.
        if (! is_array($locales)) {
            $locales = is_string($locales) ? explode(',', $locales) : [];
        }

        // Trim whitespace, lower-case the values, and remove duplicates/null entries.
        return array_values(array_unique(array_filter(array_map(
            static function ($value): string {
                if (! is_string($value)) {
                    $value = is_numeric($value) ? (string) $value : '';
                }

                return strtolower(trim($value));
            },
            $locales,
        ))));
    }

    /**
     * Ensure a slug is unique across legal translations by appending a numeric suffix when necessary.
     */
    private function ensureUniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        // Track the base slug so that suffixes can be appended without losing the original value.
        $baseSlug = $slug;
        $suffix = 1;

        // Loop until no conflicting slug exists in the data store.
        while (
            LegalTranslation::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreId,
                    static function ($query, int $id): void {
                        $query->whereKeyNot($id);
                    }
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
