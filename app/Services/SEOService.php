<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Throwable;

/**
 * SEOService
 *
 * Service class containing SEOService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class SEOService
{
    /**
     * Handle getProductSEO functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public static function getProductSEO(Product $product): array
    {
        $locale = app()->getLocale();
        $siteName = self::resolveSiteName();

        // Prefer the explicitly configured SEO title/description while still
        // falling back to a sensible default that includes the store name so
        // product pages remain descriptive in search results across locales.
        $title = self::fallbackString($product->seo_title ?? null, (string) $product->name . ' - ' . $siteName);
        $description = self::fallbackString(
            $product->seo_description ?? null,
            Str::limit(strip_tags((string) $product->description), 160)
        );

        // Convert configured keyword arrays to a comma-separated string and
        // fall back to generated keywords that reference the brand, category,
        // and SKU so crawlers receive meaningful signals even without manual
        // metadata.
        $keywords = self::normaliseKeywords($product->meta_keywords ?? null, fn (): string => self::generateProductKeywords($product));

        $slug = is_string($product->slug) && trim($product->slug) !== ''
            ? $product->slug
            : Str::slug((string) $product->name);

        // Ensure canonical URLs respect the active locale while still falling
        // back to the legacy non-localised routes if the new ones are not
        // registered (e.g. during certain test scenarios).
        $canonical = self::resolveCanonicalUrl(
            'localized.products.show',
            ['locale' => $locale, 'product' => $slug],
            ['products.show', ['product' => $slug]],
            ['product.show', $slug]
        );

        $price = (float) ($product->price ?? 0);
        $currency = function_exists('current_currency') ? current_currency() : 'EUR';

        return [
            'title'          => $title,
            'description'    => $description,
            'keywords'       => $keywords,
            'canonical'      => $canonical,
            'og_title'       => $title,
            'og_description' => $description,
            'og_image'       => $product->getFirstMediaUrl('images', 'image-lg') ?: $product->getFirstMediaUrl('images'),
            'og_type'        => 'product',
            // Present a locale-aware currency string for Open Graph previews so
            // social platforms show prices with consistent symbols and
            // separators.
            'product_price'        => Number::currency($price, $currency, locale: $locale),
            'product_currency'     => $currency,
            'product_availability' => $product->stock_quantity > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        ];
    }

    /**
     * Handle getCategorySEO functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public static function getCategorySEO(Category $category): array
    {
        $locale = app()->getLocale();
        $siteName = self::resolveSiteName();

        // Categories expose SEO-specific title/description attributes through
        // translations, so use them when available and fall back to the base
        // name/description to keep metadata complete during migrations.
        $title = self::fallbackString($category->seo_title ?? null, (string) $category->name . ' - ' . $siteName);
        $description = self::fallbackString(
            $category->seo_description ?? null,
            Str::limit(strip_tags((string) ($category->description ?? '')), 160)
        );

        $slug = is_string($category->slug) && trim($category->slug) !== ''
            ? $category->slug
            : Str::slug((string) $category->name);
        $canonical = self::resolveCanonicalUrl(
            'localized.categories.show',
            ['locale' => $locale, 'category' => $slug],
            ['categories.show', ['category' => $slug]]
        );

        return [
            'title'          => $title,
            'description'    => $description,
            'canonical'      => $canonical,
            'og_title'       => $title,
            'og_description' => $description,
            'og_image'       => $category->getFirstMediaUrl('images', 'image-lg') ?: $category->getFirstMediaUrl('images'),
            'og_type'        => 'website',
        ];
    }

    /**
     * Handle getBrandSEO functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public static function getBrandSEO(Brand $brand): array
    {
        $locale = app()->getLocale();
        $siteName = self::resolveSiteName();

        // Similar to products, brands ship dedicated SEO fields; fall back to
        // sensible defaults if they have not been authored yet.
        $title = self::fallbackString($brand->seo_title ?? null, (string) $brand->name . ' Products - ' . $siteName);
        $description = self::fallbackString(
            $brand->seo_description ?? null,
            Str::limit(strip_tags((string) ($brand->description ?? '')), 160)
        );

        $slug = is_string($brand->slug) && trim($brand->slug) !== ''
            ? $brand->slug
            : Str::slug((string) $brand->name);
        $canonical = self::resolveCanonicalUrl(
            'localized.brands.show',
            ['locale' => $locale, 'slug' => $slug],
            ['brands.show', $slug]
        );

        return [
            'title'          => $title,
            'description'    => $description,
            'canonical'      => $canonical,
            'og_title'       => $title,
            'og_description' => $description,
            'og_image'       => $brand->getFirstMediaUrl('logo', 'logo-md') ?: $brand->getFirstMediaUrl('logo'),
            'og_type'        => 'website',
        ];
    }

    /**
     * Handle generateProductKeywords functionality with proper error handling.
     */
    private static function generateProductKeywords(Product $product): string
    {
        $firstCategoryName = null;
        $categories = $product->getRelationValue('categories');
        if ($categories instanceof Collection) {
            $firstCategory = $categories->first();
            if (is_object($firstCategory) && isset($firstCategory->name) && is_string($firstCategory->name)) {
                $candidate = trim($firstCategory->name);
                $firstCategoryName = $candidate !== '' ? $candidate : null;
            }
        }

        $productName = trim($product->name);
        $brandRaw = $product->brand?->name;
        $brandName = is_string($brandRaw) ? trim($brandRaw) : '';
        $sku = is_string($product->sku) ? trim($product->sku) : '';

        $candidates = [
            $productName !== '' ? $productName : null,
            $brandName !== '' ? $brandName : null,
            $firstCategoryName,
            $sku !== '' ? $sku : null,
        ];

        $slugs = array_filter(array_map(
            static function (?string $keyword): ?string {
                if ($keyword === null || trim($keyword) === '') {
                    return null;
                }

                return Str::slug($keyword, ' ');
            },
            $candidates
        ));

        return implode(', ', $slugs);
    }

    /**
     * Handle getStructuredData functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public static function getStructuredData(Product $product): array
    {
        $currency = function_exists('current_currency') ? current_currency() : 'EUR';
        $siteName = self::resolveSiteName();

        // Schema.org requires dot-separated decimals, so normalise the price
        // irrespective of the active locale to keep structured data valid.
        $price = number_format((float) ($product->price ?? 0), 2, '.', '');

        $brandName = $product->brand?->name;
        $brandName = is_string($brandName) && trim($brandName) !== '' ? $brandName : $siteName;

        return [
            '@context'    => 'https://schema.org/',
            '@type'       => 'Product',
            'name'        => (string) $product->name,
            'description' => strip_tags((string) $product->description),
            'sku'         => $product->sku,
            'image'       => $product->getFirstMediaUrl('images', 'image-lg') ?: $product->getFirstMediaUrl('images'),
            'brand'       => ['@type' => 'Brand', 'name' => $brandName],
            'offers'      => [
                '@type'         => 'Offer',
                'price'         => $price,
                'priceCurrency' => $currency,
                'availability'  => $product->stock_quantity > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller'        => ['@type' => 'Organization', 'name' => $siteName],
            ],
        ];
    }

    /**
     * Provide a consistent fallback mechanism for nullable metadata values.
     */
    private static function fallbackString(?string $value, string $fallback): string
    {
        // Trim the value so that whitespace-only strings do not bypass the
        // fallback.
        $value = is_string($value) ? trim($value) : '';

        return $value !== '' ? $value : $fallback;
    }

    /**
     * Normalise keyword payloads into a comma-separated string.
     *
     * @param array<array-key, scalar>|string|null $keywords
     * @param callable(): string                   $fallback
     */
    private static function normaliseKeywords(null|string|array $keywords, callable $fallback): string
    {
        if (is_array($keywords)) {
            // Filter empty entries and flatten nested arrays to keep the final
            // keyword list concise and crawler friendly.
            $normalised = [];

            array_walk_recursive($keywords, static function ($keyword) use (&$normalised): void {
                if (! is_scalar($keyword)) {
                    return;
                }

                $keyword = trim((string) $keyword);
                if ($keyword !== '') {
                    $normalised[] = $keyword;
                }
            });

            $keywords = implode(', ', $normalised);
        }

        if (is_string($keywords)) {
            $keywords = trim($keywords);
        }

        if (is_string($keywords) && $keywords !== '') {
            return $keywords;
        }

        return $fallback();
    }

    /**
     * Attempt to resolve a localized canonical URL while handling fallbacks.
     *
     * @param array<string, string>                              $primaryParams
     * @param array{0: string, 1?: array<string, string>|string} ...$fallbacks  Each
     *                                                                          fallback should be provided as an array of `[routeName, params]`.
     */
    private static function resolveCanonicalUrl(string $primaryRoute, array $primaryParams, array ...$fallbacks): string
    {
        $candidates = array_merge([[$primaryRoute, $primaryParams]], $fallbacks);

        foreach ($candidates as $candidate) {
            $name = $candidate[0];
            $params = $candidate[1] ?? [];

            if ($url = self::tryRoute($name, $params)) {
                return $url;
            }
        }

        // As a last resort, fall back to the application URL so we never
        // produce an empty canonical value.
        return url('/');
    }

    /**
     * Safely generate a route URL, logging failures for observability.
     *
     * @param array<string, mixed>|string $params
     */
    private static function tryRoute(string $name, array|string $params): ?string
    {
        try {
            return route($name, $params, true);
        } catch (Throwable $exception) {
            // Log the failure without interrupting page rendering so missing
            // route definitions can be debugged without breaking storefront
            // requests.
            Log::warning('Failed to generate SEO canonical URL.', [
                'route'  => $name,
                'params' => $params,
                'error'  => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Safely resolve the application name for metadata fallbacks.
     */
    private static function resolveSiteName(): string
    {
        $value = config('app.name');

        return is_string($value) ? $value : '';
    }
}
