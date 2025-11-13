<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Storefront\Home\ProductListItemData;
use App\Models\Brand;
use App\Models\Category;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * BrandController
 *
 * HTTP controller handling BrandController related web requests, responses, and business logic with proper validation and error handling.
 */
final class BrandController extends Controller
{
    /**
     * Display the specified resource with related data.
     */
    public function show(Request $request, string $locale, string $slug): View|RedirectResponse
    {
        // Find brand by slug (check both main slug and translated slugs)
        $brand = Brand::query()->with(['translations', 'media'])->where('slug', $slug)->where('is_enabled', true)->first();
        if (! $brand) {
            // Try to find by translated slug
            $brand = Brand::query()->with(['translations', 'media'])->whereHas('translations', function ($query) use ($slug) {
                $query->where('slug', $slug)->where('locale', app()->getLocale());
            })->where('is_enabled', true)->first();
        }
        if (! $brand) {
            abort(404);
        }
        // Get canonical slug for current locale
        $canonicalSlug = $this->getCanonicalSlug($brand);
        // If the current slug is not the canonical slug, redirect
        if ($canonicalSlug !== $slug) {
            return redirect()->route('localized.brands.show', ['locale' => $locale, 'slug' => $canonicalSlug], 301);
        }
        // Load products for this brand with proper relationships
        $locale = app()->getLocale();
        try {
            $productModels = $brand->products()
                ->with([
                    'media' => function ($query): void {
                        $query->select('id', 'model_id', 'model_type', 'name', 'file_name', 'disk', 'conversions_disk', 'size', 'mime_type', 'manipulations', 'custom_properties', 'generated_conversions', 'responsive_images', 'order_column', 'created_at', 'updated_at')
                            ->where('collection_name', 'images')
                            ->orderBy('order_column');
                    },
                    'translations',
                    'brand:id,name,slug',
                    'categories:id,name,slug',
                ])
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->orderByDesc('published_at')
                ->get()
                ->filter(function ($product) {
                    // Filter out products that are not properly configured for display
                    return ! empty($product->name) &&
                           $product->is_visible &&
                           $product->price > 0 &&
                           ! empty($product->slug);
                });

            // Convert Product models to ProductListItemData DTOs
            $products = $productModels->map(fn ($product): ProductListItemData => ProductListItemData::fromModel($product, $locale));
        } catch (Exception $e) {
            // If there's an error loading products, return empty collection
            $products = collect();
        }

        // Get related categories through products
        $relatedCategories = Category::query()
            ->whereHas('products', function (Builder $query) use ($brand): void {
                $query->published()->where('brand_id', $brand->getKey());
            })
            ->withCount(['products as published_products_count' => function (Builder $builder) use ($brand): void {
                $builder->published()->where('brand_id', $brand->getKey());
            }])
            ->orderByDesc('published_products_count')
            ->limit(6)
            ->get();

        // Get SEO data
        $seoTitle = $brand->getTranslatedSeoTitle() ?: $brand->getTranslatedName() . ' - ' . config('app.name');
        $seoDescription = $brand->getTranslatedSeoDescription() ?: $brand->getTranslatedDescription();

        // Provide default values for filter/sort options
        $availableSorts = [
            'featured' => __('sort_featured'),
            'latest' => __('sort_latest'),
            'price_asc' => __('sort_price_low'),
            'price_desc' => __('sort_price_high'),
            'bestsellers' => __('sort_bestsellers'),
        ];

        $availableFilters = [
            'featured' => __('filter_featured'),
            'sale' => __('filter_sale'),
            'in_stock' => __('filter_in_stock'),
        ];

        return view('frontend.brands.show', [
            'brand'            => $brand,
            'products'         => $products,
            'relatedCategories' => $relatedCategories,
            'availableSorts'   => $availableSorts,
            'availableFilters' => $availableFilters,
            'activeSort'       => $request->get('sort', 'featured'),
            'activeFilter'     => $request->get('filter'),
            'seoTitle'         => $seoTitle,
            'seoDescription'   => $seoDescription,
        ]);
    }

    /**
     * Handle getCanonicalSlug functionality with proper error handling.
     */
    private function getCanonicalSlug(Brand $brand): string
    {
        // Get translated slug for current locale, fallback to main slug
        $translation = $brand->translations()->where('locale', app()->getLocale())->first();

        return $translation?->slug ?: $brand->slug;
    }
}
