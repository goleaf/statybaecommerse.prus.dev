<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Translations\AttributeTranslation;
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
 * AttributeTranslationController
 *
 * HTTP controller handling AttributeTranslationController related web requests, responses, and business logic with proper validation and error handling.
 */
final class AttributeTranslationController extends Controller
{
    /**
     * Persist an attribute translation while validating locale and payload.
     */
    public function update(Request $request, string $locale, int $attributeId, string $lang): RedirectResponse
    {
        // The $locale segment comes from the route prefix and does not influence how translations are stored.
        // Normalize the requested translation locale to lowercase to avoid duplicated entries.
        $targetLocale = strtolower(trim($lang));
        if ($targetLocale === '') {
            abort(404, 'Translation locale is required.');
        }

        // Build a whitelist of supported locales from configuration to prevent arbitrary locale creation.
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

        // Abort with a 404 to avoid leaking existence of administrative routes for unsupported locales.
        if (! in_array($targetLocale, $normalizedSupportedLocales, true)) {
            abort(404, 'Unsupported translation locale.');
        }

        // Ensure that the parent attribute exists even if global scopes hide it from regular queries.
        $attribute = Attribute::withoutGlobalScopes()->findOrFail($attributeId);

        // Validate and sanitize the translation payload to guard against mass-assignment and XSS vectors.
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $name = trim($validated['name']);
        if ($name === '') {
            return back()->withInput()->withErrors([
                'name' => __('validation.required', ['attribute' => 'name']),
            ]);
        }

        // Upsert the translation to keep operations idempotent for repeated submissions.
        AttributeTranslation::updateOrCreate(
            [
                'attribute_id' => $attribute->id,
                'locale'       => $targetLocale,
            ],
            [
                'name' => $name,
            ],
        );

        // Redirect back to the administration panel with a success flash message for user feedback.
        return back()->with('status', __('Attribute translation updated successfully.'));
    }
}
