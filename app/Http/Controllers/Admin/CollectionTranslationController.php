<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Translations\CollectionTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use function array_filter;
use function array_map;
use function array_unique;
use function explode;
use function in_array;
use function is_array;
use function strtolower;
use function trim;

/**
 * CollectionTranslationController
 *
 * HTTP controller handling CollectionTranslationController related web requests, responses, and business logic with proper validation and error handling.
 */
final class CollectionTranslationController extends Controller
{
    /**
     * Persist a collection translation record with validation and sanitization safeguards.
     */
    public function update(Request $request, string $locale, int $collectionId, string $lang): RedirectResponse
    {
        // Normalize the requested translation locale to avoid duplicate rows caused by casing/spacing differences.
        $targetLocale = strtolower(trim($lang));
        if ($targetLocale === '') {
            abort(404, 'Translation locale is required.');
        }

        // Resolve the list of supported locales from configuration, falling back to a sensible default when missing.
        $supportedLocales = Config::get('app.supported_locales', ['lt', 'en']);
        if (! is_array($supportedLocales)) {
            $supportedLocales = array_map(
                static fn ($value): string => trim((string) $value),
                explode(',', (string) $supportedLocales),
            );
        }
        $normalizedSupportedLocales = array_unique(array_filter(array_map(
            static fn ($value): string => strtolower(trim((string) $value)),
            $supportedLocales,
        )));

        // Abort early when an unsupported locale is requested to avoid writing inconsistent data into the database.
        if (! in_array($targetLocale, $normalizedSupportedLocales, true)) {
            abort(404, 'Unsupported translation locale.');
        }

        // Ensure that the parent collection exists even if global scopes would normally hide it from the query results.
        $collection = Collection::withoutGlobalScopes()->findOrFail($collectionId);

        // Validate incoming translation data and trim string fields to minimize noisy whitespace changes.
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords'    => ['nullable', 'array'],
            'meta_keywords.*'  => ['nullable', 'string', 'max:255'],
        ]);

        // Normalize scalar fields so empty strings become null while preserving meaningful content.
        $payload = [
            'name'             => trim($validated['name']),
            'slug'             => isset($validated['slug']) ? trim((string) $validated['slug']) ?: null : null,
            'description'      => isset($validated['description']) ? trim((string) $validated['description']) ?: null : null,
            'meta_title'       => isset($validated['meta_title']) ? trim((string) $validated['meta_title']) ?: null : null,
            'meta_description' => isset($validated['meta_description']) ? trim((string) $validated['meta_description']) ?: null : null,
        ];

        // Clean up keyword tags by removing empty entries and normalizing whitespace.
        if (isset($validated['meta_keywords'])) {
            $payload['meta_keywords'] = array_values(array_filter(array_map(
                static fn ($keyword): ?string => $keyword === null
                    ? null
                    : (trim((string) $keyword) === ''
                        ? null
                        : trim((string) $keyword)),
                $validated['meta_keywords'],
            )));
        }

        // Upsert the translation entry to keep the operation idempotent for repeated submissions.
        CollectionTranslation::updateOrCreate(
            [
                'collection_id' => $collection->id,
                'locale'        => $targetLocale,
            ],
            $payload,
        );

        // Redirect back to the previous page so Filament/Livewire panels can refresh their state with the new translation.
        return back()->with('status', __('Collection translation updated successfully.'));
    }
}
