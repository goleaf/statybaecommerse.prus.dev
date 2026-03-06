<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
    public function show(Request $request, string $slug): View|RedirectResponse
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
            return redirect()->route('localized.brands.show', ['slug' => $canonicalSlug], 301);
        }
        // Provide default values for filter/sort options.
        $availableSorts = [
            'featured'    => __('frontend.search.popular'),
            'latest'      => __('frontend.search.recent'),
            'price_asc'   => __('frontend.search.price_low_to_high'),
            'price_desc'  => __('frontend.search.price_high_to_low'),
            'bestsellers' => __('frontend.search_results.sort.relevance'),
        ];
        $availableFilters = [
            'featured' => __('frontend.search.popular'),
            'in_stock' => __('frontend.search.in_stock_only'),
        ];
        $requestedSort = (string) $request->query('sort', 'featured');
        $activeSort = array_key_exists($requestedSort, $availableSorts)
            ? $requestedSort
            : 'featured';
        $rawFilter = $request->query('filter');
        $activeFilter = is_string($rawFilter) && $rawFilter !== '' && array_key_exists($rawFilter, $availableFilters)
            ? $rawFilter
            : null;
        // Load products for this brand with proper relationships.
        // Keep the existing storefront constraints for the main listing.
        $productsQuery = $brand->products()
            ->forProductList()
            ->withListRelations()
            ->published()
            ->enabled()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->whereNotNull('slug')
            ->where('slug', '!=', '');

        if ($activeFilter === 'featured') {
            $productsQuery->where('is_featured', true);
        }
        if ($activeFilter === 'in_stock') {
            $productsQuery->where(static function (Builder $query): void {
                $query->where('manage_stock', false)
                    ->orWhere('stock_quantity', '>', 0);
            });
        }
        match ($activeSort) {
            'latest'      => $productsQuery->orderByDesc('published_at'),
            'price_asc'   => $productsQuery->orderBy('price')->orderBy('name'),
            'price_desc'  => $productsQuery->orderByDesc('price')->orderBy('name'),
            'bestsellers' => $productsQuery
                ->withSum('orderItems as sales_count', 'quantity')
                ->orderByDesc('sales_count')
                ->orderByDesc('published_at'),
            default => $productsQuery->orderByDesc('is_featured')->orderByDesc('published_at'),
        };

        $products = $productsQuery
            ->paginate(12)
            ->withQueryString();

        // Get related categories/subcategories for this brand in ascending order.
        $relatedCategories = Category::query()
            ->whereHas('products', function (Builder $query) use ($brand): void {
                $query->published()->where('brand_id', $brand->getKey());
            })
            ->withCount(['products as published_products_count' => function (Builder $builder) use ($brand): void {
                $builder->published()->where('brand_id', $brand->getKey());
            }])
            ->orderBy('name')
            ->get();

        // If the main brand grid is empty, prepare fallback sections:
        // 8 products per category/subcategory, newest first by created_at.
        $categoryProductSections = collect();
        if ($products->isEmpty() && $relatedCategories->isNotEmpty() && $activeFilter === null) {
            $categoryProductSections = $this->buildCategoryProductSections($brand, $relatedCategories);
        }

        // Get SEO data
        $seoTitle = $brand->getTranslatedSeoTitle() ?: $brand->getTranslatedName() . ' - ' . config('app.name');
        $seoDescription = $brand->getTranslatedSeoDescription() ?: $brand->getTranslatedDescription();

        return view('frontend.brands.show', [
            'brand'                   => $brand,
            'products'                => $products,
            'relatedCategories'       => $relatedCategories,
            'categoryProductSections' => $categoryProductSections,
            'availableSorts'          => $availableSorts,
            'availableFilters'        => $availableFilters,
            'activeSort'              => $activeSort,
            'activeFilter'            => $activeFilter,
            'seoTitle'                => $seoTitle,
            'seoDescription'          => $seoDescription,
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

    /**
     * Build fallback sections grouped by category for brands whose direct listing is empty.
     *
     * @return Collection<int, array{category: Category, products: Collection<int, Product>}>
     */
    private function buildCategoryProductSections(Brand $brand, Collection $categories): Collection
    {
        return $categories
            ->map(function (Category $category) use ($brand): array {
                $products = Product::query()
                    ->with(['brand', 'media', 'primaryImage'])
                    ->published()
                    ->enabled()
                    ->where('brand_id', $brand->getKey())
                    ->whereHas('categories', static function (Builder $query) use ($category): void {
                        $query->where('categories.id', $category->getKey());
                    })
                    ->orderByDesc('created_at')
                    ->limit(8)
                    ->get();

                return [
                    'category' => $category,
                    'products' => $products,
                ];
            })
            ->filter(static fn (array $section): bool => $section['products']->isNotEmpty())
            ->values();
    }
}
