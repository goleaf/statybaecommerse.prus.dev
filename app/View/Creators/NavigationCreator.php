<?php

declare(strict_types=1);

namespace App\View\Creators;

use App\Models\Category;
use App\Models\Brand;
use App\Services\Shared\CacheService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\LazyCollection;

/**
 * NavigationCreator
 * 
 * View Creator that provides navigation data to views that need it.
 * This includes categories, brands, and other navigation elements.
 */
final class NavigationCreator
{
    public function __construct(
        private readonly CacheService $cacheService
    ) {}

    /**
     * Create the view creator.
     */
    public function create(View $view): void
    {
        // Only add navigation data to specific views to avoid unnecessary queries
        $viewName = $view->getName();
        
        if ($this->shouldIncludeNavigationData($viewName)) {
            $view->with([
                'topCategories' => $this->getTopCategories(),
                'featuredBrands' => $this->getFeaturedBrands(),
                'navigationMenu' => $this->getNavigationMenu(),
            ]);
        }
    }

    /**
     * Determine if navigation data should be included for this view.
     */
    private function shouldIncludeNavigationData(string $viewName): bool
    {
        $navigationViews = [
            'components.layouts.base',
            'components.layouts.header',
            'components.layouts.navigation',
            'livewire.components.enhanced-navigation',
            'shop.index',
            'products.index',
            'products.show',
            'categories.show',
            'brands.show',
        ];

        return in_array($viewName, $navigationViews) || 
               str_starts_with($viewName, 'components.layouts.') ||
               str_starts_with($viewName, 'livewire.components.');
    }

    /**
     * Get top-level categories for navigation.
     */
    private function getTopCategories()
    {
        return $this->cacheService->rememberLong(
            'navigation.top_categories.' . app()->getLocale(),
            fn () => Category::query()
                ->with(['translations' => function ($q) {
                    $q->where('locale', app()->getLocale());
                }])
                ->where('is_visible', true)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->limit(8)
                ->cursor()
                ->takeUntilTimeout(now()->addSeconds(5))
                ->collect()
        );
    }

    /**
     * Get featured brands for navigation.
     */
    private function getFeaturedBrands()
    {
        return $this->cacheService->rememberLong(
            'navigation.featured_brands.' . app()->getLocale(),
            fn () => Brand::query()
                ->with(['translations' => function ($q) {
                    $q->where('locale', app()->getLocale());
                }])
                ->where('is_enabled', true)
                ->where('is_featured', true)
                ->orderBy('sort_order')
                ->limit(6)
                ->cursor()
                ->takeUntilTimeout(now()->addSeconds(5))
                ->collect()
        );
    }

    /**
     * Get complete navigation menu structure.
     */
    private function getNavigationMenu(): array
    {
        return $this->cacheService->rememberLong(
            'navigation.menu.' . app()->getLocale(),
            function () {
                $categories = $this->getTopCategories();
                $brands = $this->getFeaturedBrands();

                return [
                    'categories' => $categories->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->getTranslatedName(),
                            'slug' => $category->slug,
                            'url' => route('categories.show', $category->slug),
                            'icon' => $category->icon,
                            'children' => $category->children()
                                ->where('is_visible', true)
                                ->orderBy('sort_order')
                                ->limit(5)
                                ->cursor()
                                ->takeUntilTimeout(now()->addSeconds(5))
                                ->collect()
                                ->map(fn ($child) => [
                                    'id' => $child->id,
                                    'name' => $child->getTranslatedName(),
                                    'slug' => $child->slug,
                                    'url' => route('categories.show', $child->slug),
                                ]),
                        ];
                    }),
                    'brands' => $brands->map(function ($brand) {
                        return [
                            'id' => $brand->id,
                            'name' => $brand->getTranslatedName(),
                            'slug' => $brand->slug,
                            'url' => route('brands.show', $brand->slug),
                            'logo' => $brand->logo_url,
                        ];
                    }),
                ];
            }
        );
    }
}
