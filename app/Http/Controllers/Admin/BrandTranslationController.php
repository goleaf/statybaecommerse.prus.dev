<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Translations\BrandTranslation;

use function array_key_exists;
use function array_unique;
use function explode;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

use function in_array;
use function is_array;
use function is_float;
use function is_int;
use function is_numeric;
use function is_object;
use function method_exists;
use function strtolower;
use function trim;

/**
 * BrandTranslationController
 *
 * HTTP controller handling BrandTranslationController related web requests, responses, and business logic with proper validation and error handling.
 */
final class BrandTranslationController extends Controller
{
    /**
     * Persist a brand translation while validating locale and payload.
     */
    public function update(Request $request, string $locale, int $brandId, string $lang): RedirectResponse
    {
        // Normalize the requested translation locale so that repeated submissions remain idempotent.
        $targetLocale = strtolower(trim($lang));
        if ($targetLocale === '') {
            abort(404, 'Translation locale is required.');
        }

        // Read the configured locales and normalize them to avoid storing arbitrary or unsupported languages.
        $configuredLocales = Config::get('app.supported_locales', ['lt', 'en']);
        if (is_array($configuredLocales)) {
            $localeCandidates = $configuredLocales;
        } elseif (is_string($configuredLocales)) {
            $localeCandidates = explode(',', $configuredLocales);
        } elseif (is_numeric($configuredLocales)) {
            $localeCandidates = [(string) $configuredLocales];
        } elseif (is_object($configuredLocales) && method_exists($configuredLocales, '__toString')) {
            $localeCandidates = [(string) $configuredLocales];
        } else {
            $localeCandidates = [];
        }
        $normalizedSupportedLocales = [];
        foreach ($localeCandidates as $value) {
            // Only allow scalar values or stringable objects to be converted into locale codes.
            if (is_string($value)) {
                $candidate = strtolower(trim($value));
            } elseif (is_numeric($value)) {
                $candidate = strtolower(trim((string) $value));
            } elseif (is_object($value) && method_exists($value, '__toString')) {
                $candidate = strtolower(trim((string) $value));
            } else {
                continue;
            }

            if ($candidate !== '') {
                $normalizedSupportedLocales[] = $candidate;
            }
        }
        $normalizedSupportedLocales = array_unique($normalizedSupportedLocales);

        // Abort with a 404 to hide administrative routes when the locale is not supported.
        if (! in_array($targetLocale, $normalizedSupportedLocales, true)) {
            abort(404, 'Unsupported translation locale.');
        }

        // Ensure that the parent brand exists even if global scopes would normally filter it out.
        $brand = Brand::withoutGlobalScopes()->findOrFail($brandId);

        // Validate the translation payload to guard against mass-assignment and malformed data.
        /**
         * @var array{
         *     name: string,
         *     slug?: string|null,
         *     description?: string|null,
         *     seo_title?: string|null,
         *     seo_description?: string|null
         * } $validated
         */
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'slug'            => ['nullable', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'seo_title'       => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
        ]);

        // Trim the name so that whitespace-only submissions trigger validation feedback.
        $name = trim($validated['name']);
        if ($name === '') {
            return back()->withInput()->withErrors([
                'name' => __('validation.required', ['attribute' => 'name']),
            ]);
        }

        // Sanitize the slug while keeping a deterministic fallback if the provided value is empty.
        $slug = null;
        if (array_key_exists('slug', $validated) && is_string($validated['slug'])) {
            $rawSlug = trim($validated['slug']);
            if ($rawSlug !== '') {
                $slug = Str::slug($rawSlug);
            }
        }
        if ($slug === null || $slug === '') {
            $fallbackSource = trim((string) ($brand->slug ?? ''));
            if ($fallbackSource === '') {
                $fallbackSource = trim((string) $brand->name);
            }
            if ($fallbackSource === '') {
                $fallbackSource = $name;
            }
            $slug = Str::slug($fallbackSource);
        }
        if ($slug === '') {
            $brandIdentifier = $this->resolveBrandIdentifier($brand, $brandId);
            $slug = 'brand-' . $brandIdentifier . '-' . $targetLocale;
        }

        // Normalize optional fields so that empty strings are stored as NULL values.
        $description = $validated['description'] ?? null;
        if (is_string($description)) {
            $description = trim($description);
            $description = $description === '' ? null : $description;
        } else {
            $description = null;
        }

        $seoTitle = $validated['seo_title'] ?? null;
        if (is_string($seoTitle)) {
            $seoTitle = trim($seoTitle);
            $seoTitle = $seoTitle === '' ? null : $seoTitle;
        } else {
            $seoTitle = null;
        }

        $seoDescription = $validated['seo_description'] ?? null;
        if (is_string($seoDescription)) {
            $seoDescription = trim($seoDescription);
            $seoDescription = $seoDescription === '' ? null : $seoDescription;
        } else {
            $seoDescription = null;
        }

        // Upsert the translation record so repeated submissions update the same row instead of creating duplicates.
        BrandTranslation::updateOrCreate(
            [
                'brand_id' => $brand->getKey(),
                'locale'   => $targetLocale,
            ],
            [
                'name'            => $name,
                'slug'            => $slug,
                'description'     => $description,
                'seo_title'       => $seoTitle,
                'seo_description' => $seoDescription,
            ],
        );

        // Redirect back to the previous page with user-facing feedback about the successful operation.
        return back()->with('status', __('Brand translation updated successfully.'));
    }

    /**
     * Resolve a stable identifier used when auto-generating slug fragments.
     */
    private function resolveBrandIdentifier(Brand $brand, int $fallbackId): string
    {
        // Attempt to reuse the brand's primary key if it is already stored as a non-empty string.
        $brandKey = $brand->getKey();
        if (is_string($brandKey)) {
            $trimmedKey = trim($brandKey);
            if ($trimmedKey !== '') {
                return $trimmedKey;
            }
        }

        // Accept numeric primary keys directly to keep generated slugs deterministic.
        if (is_int($brandKey) || is_float($brandKey)) {
            return (string) $brandKey;
        }

        // Fall back to the identifier from the route when the primary key is unavailable.
        return (string) $fallbackId;
    }
}
