<?php

declare(strict_types=1);

namespace App\View\Creators;

use App\Services\SEOService;
use Illuminate\Contracts\View\View;

/**
 * SeoDataCreator
 *
 * View Creator that provides SEO data to views.
 * This includes meta tags, structured data, and SEO-related information.
 */
final class SeoDataCreator
{
    public function __construct(
        private readonly SEOService $seoService
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
            'seo' => $seoData,
            'metaTitle' => $seoData['title'],
            'metaDescription' => $seoData['description'],
            'metaKeywords' => $seoData['keywords'],
            'canonicalUrl' => $seoData['canonical_url'],
            'ogTitle' => $seoData['og_title'],
            'ogDescription' => $seoData['og_description'],
            'ogImage' => $seoData['og_image'],
            'ogType' => $seoData['og_type'],
            'twitterCard' => $seoData['twitter_card'],
            'structuredData' => $seoData['structured_data'],
        ]);
    }

    /**
     * Generate SEO data based on view name and data.
     */
    private function generateSeoData(string $viewName, array $viewData): array
    {
        $defaultSeo = [
            'title' => config('app.name'),
            'description' => __('seo.default_description'),
            'keywords' => __('seo.default_keywords'),
            'canonical_url' => request()->url(),
            'og_title' => config('app.name'),
            'og_description' => __('seo.default_description'),
            'og_image' => asset('images/og-default.jpg'),
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'structured_data' => [],
        ];

        // Generate view-specific SEO data
        $seoData = match (true) {
            str_contains($viewName, 'products.show') => $this->getProductSeoData($viewData),
            str_contains($viewName, 'categories.show') => $this->getCategorySeoData($viewData),
            str_contains($viewName, 'brands.show') => $this->getBrandSeoData($viewData),
            str_contains($viewName, 'shop.index') => $this->getShopSeoData($viewData),
            str_contains($viewName, 'users.dashboard') => $this->getUserDashboardSeoData($viewData),
            default => $defaultSeo,
        };

        // Merge with default data
        return array_merge($defaultSeo, $seoData);
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
            'title' => $product->getTranslatedName().' - '.config('app.name'),
            'description' => $product->getTranslatedDescription() ?: __('seo.product_default_description', ['name' => $product->getTranslatedName()]),
            'keywords' => $this->generateProductKeywords($product),
            'og_title' => $product->getTranslatedName(),
            'og_description' => $product->getTranslatedDescription() ?: __('seo.product_default_description', ['name' => $product->getTranslatedName()]),
            'og_image' => $product->featured_image_url ?: asset('images/og-default.jpg'),
            'og_type' => 'product',
            'structured_data' => $this->generateProductStructuredData($product),
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
            'title' => $category->getTranslatedName().' - '.config('app.name'),
            'description' => $category->getTranslatedDescription() ?: __('seo.category_default_description', ['name' => $category->getTranslatedName()]),
            'keywords' => $this->generateCategoryKeywords($category),
            'og_title' => $category->getTranslatedName(),
            'og_description' => $category->getTranslatedDescription() ?: __('seo.category_default_description', ['name' => $category->getTranslatedName()]),
            'og_image' => $category->image_url ?: asset('images/og-default.jpg'),
            'structured_data' => $this->generateCategoryStructuredData($category),
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
            'title' => $brand->getTranslatedName().' - '.config('app.name'),
            'description' => $brand->getTranslatedDescription() ?: __('seo.brand_default_description', ['name' => $brand->getTranslatedName()]),
            'keywords' => $this->generateBrandKeywords($brand),
            'og_title' => $brand->getTranslatedName(),
            'og_description' => $brand->getTranslatedDescription() ?: __('seo.brand_default_description', ['name' => $brand->getTranslatedName()]),
            'og_image' => $brand->logo_url ?: asset('images/og-default.jpg'),
            'structured_data' => $this->generateBrandStructuredData($brand),
        ];
    }

    /**
     * Get SEO data for shop pages.
     */
    private function getShopSeoData(array $viewData): array
    {
        return [
            'title' => __('seo.shop_title').' - '.config('app.name'),
            'description' => __('seo.shop_description'),
            'keywords' => __('seo.shop_keywords'),
            'og_title' => __('seo.shop_title'),
            'og_description' => __('seo.shop_description'),
            'structured_data' => $this->generateShopStructuredData(),
        ];
    }

    /**
     * Get SEO data for user dashboard.
     */
    private function getUserDashboardSeoData(array $viewData): array
    {
        return [
            'title' => __('seo.dashboard_title').' - '.config('app.name'),
            'description' => __('seo.dashboard_description'),
            'robots' => 'noindex, nofollow', // Don't index user dashboards
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
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->getTranslatedName(),
            'description' => $product->getTranslatedDescription(),
            'image' => $product->featured_image_url,
            'brand' => [
                '@type' => 'Brand',
                'name' => $product->brand?->getTranslatedName(),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $product->price,
                'priceCurrency' => current_currency(),
                'availability' => $product->is_in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ],
        ];
    }

    /**
     * Generate category structured data.
     */
    private function generateCategoryStructuredData($category): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $category->getTranslatedName(),
            'description' => $category->getTranslatedDescription(),
        ];
    }

    /**
     * Generate brand structured data.
     */
    private function generateBrandStructuredData($brand): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Brand',
            'name' => $brand->getTranslatedName(),
            'description' => $brand->getTranslatedDescription(),
            'logo' => $brand->logo_url,
        ];
    }

    /**
     * Generate shop structured data.
     */
    private function generateShopStructuredData(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Store',
            'name' => config('app.name'),
            'description' => __('seo.shop_description'),
            'url' => config('app.url'),
        ];
    }
}
