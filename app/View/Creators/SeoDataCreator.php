<?php

declare(strict_types=1);

namespace App\View\Creators;

use App\Services\SEOService;
use App\Support\Seo\LocaleUrlGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

/**
 * SeoDataCreator
 *
 * View Creator that provides SEO data to views.
 * This includes meta tags, structured data, and SEO-related information.
 */
final class SeoDataCreator
{
    public function __construct(
        private readonly SEOService $seoService,
        private readonly LocaleUrlGenerator $localeUrlGenerator
    ) {}

    /**
     * Create the view creator.
     */
    public function create(View $view): void
    {
        $viewName = $view->getName();
        $viewData = $view->getData();

        $seoData = $this->generateSeoData($viewName, $viewData);

        $view->with([
            'seo'             => $seoData,
            'metaTitle'       => $seoData['title'],
            'metaDescription' => $seoData['description'],
            'metaKeywords'    => is_array($seoData['keywords'])
                ? implode(', ', array_filter(array_map(static fn ($keyword): string => trim((string) $keyword), $seoData['keywords'])))
                : (string) $seoData['keywords'],
            'canonicalUrl'     => $seoData['canonical_url'],
            'ogTitle'          => $seoData['og_title'],
            'ogDescription'    => $seoData['og_description'],
            'ogImage'          => $seoData['og_image'],
            'ogType'           => $seoData['og_type'],
            'twitterCard'      => $seoData['twitter_card'],
            'structuredData'   => $seoData['structured_data'],
            'alternateLocales' => $seoData['alternate_locales'],
        ]);
    }

    /**
     * Generate SEO data based on view name and data.
     */
    private function generateSeoData(string $viewName, array $viewData): array
    {
        $defaultSeo = [
            'title'             => config('app.name'),
            'description'       => __('seo.default_description'),
            'keywords'          => __('seo.default_keywords'),
            'canonical_url'     => request()->url(),
            'og_title'          => config('app.name'),
            'og_description'    => __('seo.default_description'),
            'og_image'          => og_placeholder_url(),
            'og_type'           => 'website',
            'twitter_card'      => 'summary_large_image',
            'structured_data'   => [],
            'alternate_locales' => $this->localeUrlGenerator->fallbackAlternateLocales(),
        ];

        // Generate view-specific SEO data
        $seoData = match (true) {
            str_contains($viewName, 'products.show')   => $this->getProductSeoData($viewData),
            str_contains($viewName, 'categories.show') => $this->getCategorySeoData($viewData),
            str_contains($viewName, 'brands.show')     => $this->getBrandSeoData($viewData),
            str_contains($viewName, 'shop.index')      => $this->getShopSeoData($viewData),
            str_contains($viewName, 'users.dashboard') => $this->getUserDashboardSeoData($viewData),
            default                                    => $defaultSeo,
        };

        // Merge with default data
        $resolvedSeo = array_merge($defaultSeo, $seoData);

        if (! isset($resolvedSeo['canonical_url']) || ! is_string($resolvedSeo['canonical_url'])) {
            $resolvedSeo['canonical_url'] = $this->resolveCanonicalUrl();
        }

        if (! isset($resolvedSeo['alternate_locales']) || ! is_array($resolvedSeo['alternate_locales']) || $resolvedSeo['alternate_locales'] === []) {
            $resolvedSeo['alternate_locales'] = $this->resolveAlternateLocales($viewData);
        }

        return $resolvedSeo;
    }

    /**
     * Get SEO data for product pages.
     */
    private function getProductSeoData(array $viewData): array
    {
        $product = $viewData['product'] ?? null;

        if (! $product) {
            return [];
        }

        return [
            'title'           => $product->getTranslatedName() . ' - ' . config('app.name'),
            'description'     => $product->getTranslatedDescription() ?: __('seo.product_default_description', ['name' => $product->getTranslatedName()]),
            'keywords'        => $this->generateProductKeywords($product),
            'og_title'        => $product->getTranslatedName(),
            'og_description'  => $product->getTranslatedDescription() ?: __('seo.product_default_description', ['name' => $product->getTranslatedName()]),
            'og_image'        => $product->featured_image_url ?: og_placeholder_url(),
            'og_type'         => 'product',
            'structured_data' => $this->generateProductStructuredData($product),
            'canonical_url'   => $this->localeUrlGenerator->localizedRoute(
                'localized.products.show',
                ['product' => $this->localeUrlGenerator->translatedValue($product, app()->getLocale(), 'getTranslatedSlug', 'slug', 'slug') ?? $product->slug],
                app()->getLocale()
            ) ?: route('frontend.products.show', $product),
            'alternate_locales' => $this->localeUrlGenerator->generateAlternates(
                'localized.products.show',
                fn (string $locale) => (
                    ($slug = $this->localeUrlGenerator->translatedValue($product, $locale, 'getTranslatedSlug', 'slug', 'slug'))
                ) ? ['product' => $slug] : null
            ),
        ];
    }

    /**
     * Get SEO data for category pages.
     */
    private function getCategorySeoData(array $viewData): array
    {
        $category = $viewData['category'] ?? null;

        if (! $category) {
            return [];
        }

        return [
            'title'           => $category->getTranslatedName() . ' - ' . config('app.name'),
            'description'     => $category->getTranslatedDescription() ?: __('seo.category_default_description', ['name' => $category->getTranslatedName()]),
            'keywords'        => $this->generateCategoryKeywords($category),
            'og_title'        => $category->getTranslatedName(),
            'og_description'  => $category->getTranslatedDescription() ?: __('seo.category_default_description', ['name' => $category->getTranslatedName()]),
            'og_image'        => $category->image_url ?: og_placeholder_url(),
            'structured_data' => $this->generateCategoryStructuredData($category),
            'canonical_url'   => $this->localeUrlGenerator->localizedRoute(
                'localized.categories.show',
                ['category' => $this->localeUrlGenerator->translatedValue($category, app()->getLocale(), 'getTranslatedSlug', 'slug', 'slug') ?? $category->slug],
                app()->getLocale()
            ) ?: route('frontend.categories.show', $category),
            'alternate_locales' => $this->localeUrlGenerator->generateAlternates(
                'localized.categories.show',
                fn (string $locale) => (
                    ($slug = $this->localeUrlGenerator->translatedValue($category, $locale, 'getTranslatedSlug', 'slug', 'slug'))
                ) ? ['category' => $slug] : null
            ),
        ];
    }

    /**
     * Get SEO data for brand pages.
     */
    private function getBrandSeoData(array $viewData): array
    {
        $brand = $viewData['brand'] ?? null;

        if (! $brand) {
            return [];
        }

        return [
            'title'           => $brand->getTranslatedName() . ' - ' . config('app.name'),
            'description'     => $brand->getTranslatedDescription() ?: __('seo.brand_default_description', ['name' => $brand->getTranslatedName()]),
            'keywords'        => $this->generateBrandKeywords($brand),
            'og_title'        => $brand->getTranslatedName(),
            'og_description'  => $brand->getTranslatedDescription() ?: __('seo.brand_default_description', ['name' => $brand->getTranslatedName()]),
            'og_image'        => $brand->logo_url ?: og_placeholder_url(),
            'structured_data' => $this->generateBrandStructuredData($brand),
            'canonical_url'   => $this->localeUrlGenerator->localizedRoute(
                'localized.brands.show',
                ['slug' => $this->localeUrlGenerator->translatedValue($brand, app()->getLocale(), 'getTranslatedSlug', 'slug', 'slug') ?? $brand->slug],
                app()->getLocale()
            ) ?: route('frontend.brands.show', $brand),
            'alternate_locales' => $this->localeUrlGenerator->generateAlternates(
                'localized.brands.show',
                fn (string $locale) => (
                    ($slug = $this->localeUrlGenerator->translatedValue($brand, $locale, 'getTranslatedSlug', 'slug', 'slug'))
                ) ? ['slug' => $slug] : null
            ),
        ];
    }

    /**
     * Get SEO data for shop pages.
     */
    private function getShopSeoData(array $viewData): array
    {
        return [
            'title'             => __('seo.shop_title') . ' - ' . config('app.name'),
            'description'       => __('seo.shop_description'),
            'keywords'          => __('seo.shop_keywords'),
            'og_title'          => __('seo.shop_title'),
            'og_description'    => __('seo.shop_description'),
            'structured_data'   => $this->generateShopStructuredData(),
            'canonical_url'     => $this->localeUrlGenerator->localizedRoute('localized.home', [], app()->getLocale()) ?: url('/' . app()->getLocale()),
            'alternate_locales' => $this->localeUrlGenerator->generateAlternates(
                'localized.home',
                fn (string $locale) => []
            ),
        ];
    }

    private function resolveCanonicalUrl(): string
    {
        $route = request()->route();
        if (! $route) {
            return url()->current();
        }

        $name = $route->getName();
        $locale = app()->getLocale();
        $parameters = $route->parameters();

        if (is_string($name) && str_starts_with($name, 'localized.')) {
            return route($name, array_merge($parameters, ['locale' => $locale]));
        }

        if (is_string($name) && str_starts_with($name, 'frontend.')) {
            $localizedName = str_replace('frontend.', 'localized.', $name);
            if ($localizedName !== $name && Route::has($localizedName)) {
                return route($localizedName, array_merge($parameters, ['locale' => $locale]));
            }
        }

        return url()->current();
    }

    /**
     * @param  array<string, mixed>  $viewData
     * @return array<string, string>
     */
    private function resolveAlternateLocales(array $viewData): array
    {
        $route = request()->route();
        if (! $route) {
            return $this->localeUrlGenerator->fallbackAlternateLocales();
        }

        $name = $route->getName();
        $parameters = $route->parameters();

        if (isset($viewData['product'])) {
            return $this->localeUrlGenerator->generateAlternates(
                'localized.products.show',
                fn (string $locale) => (
                    ($slug = $this->localeUrlGenerator->translatedValue($viewData['product'], $locale, 'getTranslatedSlug', 'slug', 'slug'))
                ) ? ['product' => $slug] : null
            );
        }

        if (isset($viewData['category'])) {
            return $this->localeUrlGenerator->generateAlternates(
                'localized.categories.show',
                fn (string $locale) => (
                    ($slug = $this->localeUrlGenerator->translatedValue($viewData['category'], $locale, 'getTranslatedSlug', 'slug', 'slug'))
                ) ? ['category' => $slug] : null
            );
        }

        if (isset($viewData['brand'])) {
            return $this->localeUrlGenerator->generateAlternates(
                'localized.brands.show',
                fn (string $locale) => (
                    ($slug = $this->localeUrlGenerator->translatedValue($viewData['brand'], $locale, 'getTranslatedSlug', 'slug', 'slug'))
                ) ? ['slug' => $slug] : null
            );
        }

        if (is_string($name) && str_starts_with($name, 'localized.')) {
            return $this->localeUrlGenerator->generateAlternates(
                $name,
                fn (string $locale) => array_merge($parameters, ['locale' => $locale])
            );
        }

        return $this->localeUrlGenerator->fallbackAlternateLocales();
    }

    /**
     * Get SEO data for user dashboard.
     */
    private function getUserDashboardSeoData(array $viewData): array
    {
        return [
            'title'       => __('seo.dashboard_title') . ' - ' . config('app.name'),
            'description' => __('seo.dashboard_description'),
            'robots'      => 'noindex, nofollow', // Don't index user dashboards
        ];
    }

    /**
     * Generate product keywords.
     */
    private function generateProductKeywords($product): string
    {
        $keywords = [
            $product->getTranslatedName(),
            $product->brand?->getTranslatedName(),
            $product->category?->getTranslatedName(),
        ];

        return implode(', ', array_filter($keywords));
    }

    /**
     * Generate category keywords.
     */
    private function generateCategoryKeywords($category): string
    {
        $keywords = [
            $category->getTranslatedName(),
            __('seo.category_keywords'),
        ];

        return implode(', ', array_filter($keywords));
    }

    /**
     * Generate brand keywords.
     */
    private function generateBrandKeywords($brand): string
    {
        $keywords = [
            $brand->getTranslatedName(),
            __('seo.brand_keywords'),
        ];

        return implode(', ', array_filter($keywords));
    }

    /**
     * Generate product structured data.
     */
    private function generateProductStructuredData($product): array
    {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $product->getTranslatedName(),
            'description' => $product->getTranslatedDescription(),
            'image'       => $product->featured_image_url,
            'brand'       => [
                '@type' => 'Brand',
                'name'  => $product->brand?->getTranslatedName(),
            ],
            'offers' => [
                '@type'         => 'Offer',
                'price'         => $product->price,
                'priceCurrency' => current_currency(),
                'availability'  => $product->is_in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ],
        ];
    }

    /**
     * Generate category structured data.
     */
    private function generateCategoryStructuredData($category): array
    {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'CollectionPage',
            'name'        => $category->getTranslatedName(),
            'description' => $category->getTranslatedDescription(),
        ];
    }

    /**
     * Generate brand structured data.
     */
    private function generateBrandStructuredData($brand): array
    {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Brand',
            'name'        => $brand->getTranslatedName(),
            'description' => $brand->getTranslatedDescription(),
            'logo'        => $brand->logo_url,
        ];
    }

    /**
     * Generate shop structured data.
     */
    private function generateShopStructuredData(): array
    {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Store',
            'name'        => config('app.name'),
            'description' => __('seo.shop_description'),
            'url'         => config('app.url'),
        ];
    }
}
