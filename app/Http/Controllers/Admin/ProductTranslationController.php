<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Translations\ProductTranslation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

use function array_filter;
use function array_map;
use function array_unique;
use function explode;
use function in_array;
use function is_array;
use function strtolower;
use function trim;

/**
 * ProductTranslationController
 *
 * HTTP controller handling ProductTranslationController related web requests, responses, and business logic with proper validation and error handling.
 */
final class ProductTranslationController extends Controller
{
    /**
     * Persist a product translation ensuring locale validation and sanitized payload data.
     */
    public function update(Request $request, string $locale, int $productId, string $lang): RedirectResponse
    {
        // Normalize the requested translation locale to avoid duplicate entries caused by casing differences.
        $targetLocale = strtolower(trim($lang));
        if ($targetLocale === '') {
            abort(404, 'Translation locale is required.');
        }

        // Resolve the supported locales from configuration to guard against arbitrary locale creation attempts.
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

        // Abort with a 404 for unsupported locales to avoid leaking administrative routes.
        if (! in_array($targetLocale, $normalizedSupportedLocales, true)) {
            abort(404, 'Unsupported translation locale.');
        }

        // Retrieve the product without global scopes because hidden products may still require translation updates.
        $product = Product::withoutGlobalScopes()->findOrFail($productId);

        // Validate incoming data to prevent mass-assignment vulnerabilities and ensure data quality.
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'slug'               => ['nullable', 'string', 'max:255'],
            'summary'            => ['nullable', 'string'],
            'description'        => ['nullable', 'string'],
            'short_description'  => ['nullable', 'string'],
            'seo_title'          => ['nullable', 'string', 'max:255'],
            'seo_description'    => ['nullable', 'string'],
            'meta_keywords'      => ['nullable', 'array'],
            'meta_keywords.*'    => ['nullable', 'string'],
            'alt_text'           => ['nullable', 'string', 'max:255'],
        ]);

        // Trim the required name field once more so UI level trimming discrepancies never persist to storage.
        $name = trim($validated['name']);
        if ($name === '') {
            return back()->withInput()->withErrors([
                'name' => __('validation.required', ['attribute' => 'name']),
            ]);
        }

        // Generate a slug fallback based on the provided name when the request leaves it blank.
        $slug = trim((string) ($validated['slug'] ?? ''));
        if ($slug === '') {
            $slug = Str::slug($name);
        }

        // Sanitize optional long-form text inputs by trimming whitespace while preserving intentional formatting.
        $optionalText = static function (?string $value): ?string {
            if ($value === null) {
                return null;
            }

            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        };

        // Filter meta keywords to remove empty entries and normalize whitespace for consistent SEO data.
        $metaKeywords = [];
        foreach (($validated['meta_keywords'] ?? []) as $keyword) {
            if (! is_string($keyword)) {
                continue;
            }

            $normalizedKeyword = trim($keyword);
            if ($normalizedKeyword !== '') {
                $metaKeywords[] = $normalizedKeyword;
            }
        }

        // Persist the translation using an upsert to keep the operation idempotent when editors retry submissions.
        ProductTranslation::updateOrCreate(
            [
                'product_id' => $product->id,
                'locale'     => $targetLocale,
            ],
            [
                'name'              => $name,
                'slug'              => $slug,
                'summary'           => $optionalText($validated['summary'] ?? null),
                'description'       => $optionalText($validated['description'] ?? null),
                'short_description' => $optionalText($validated['short_description'] ?? null),
                'seo_title'         => $optionalText($validated['seo_title'] ?? null),
                'seo_description'   => $optionalText($validated['seo_description'] ?? null),
                'meta_keywords'     => $metaKeywords,
                'alt_text'          => $optionalText($validated['alt_text'] ?? null),
            ],
        );

        // Redirect back to the admin panel with feedback so editors receive immediate confirmation.
        return back()->with('status', __('Product translation updated successfully.'));
    }
}
