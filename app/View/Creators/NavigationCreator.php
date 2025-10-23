<?php

declare(strict_types=1);

namespace App\View\Creators;

use App\Models\Brand;
use App\Repositories\CategoryRepository;
use App\Repositories\MenuRepository;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

/**
 * NavigationCreator
 *
 * View Creator that provides navigation data to views that need it.
 * This includes categories, brands, and other navigation elements.
 */
final class NavigationCreator
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly MenuRepository $menuRepository,
    ) {}

    /**
     * Create the view creator.
     */
    public function create(View $view): void
    {
        // Only add navigation data to specific views to avoid unnecessary queries
        $viewName = $view->getName();

        if ($this->shouldIncludeNavigationData($viewName)) {
            $topCategories = $this->getTopCategories();
            $featuredBrands = $this->getFeaturedBrands();
            $menus = $this->menuRepository->all(locale: app()->getLocale());

            $view->with([
                'topCategories'  => $topCategories,
                'featuredBrands' => $featuredBrands,
                'navigationMenu' => [
                    'categories' => $topCategories,
                    'brands'     => $featuredBrands,
                    'menus'      => $menus,
                ],
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
        return $this->categoryRepository->navigation(8);
    }

    /**
     * Get featured brands for navigation.
     */
    private function getFeaturedBrands()
    {
        $cacheKey = 'navigation.featured_brands.' . app()->getLocale();
        $ttl = now()->addMinutes(30);
        $callback = fn () => Brand::query()
            ->with(['translations' => function ($q) {
                $q->where('locale', app()->getLocale());
            }])
            ->where('is_enabled', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->cursor()
            ->takeUntilTimeout(now()->addSeconds(5))
            ->collect();

        if (Cache::supportsTags()) {
            return Cache::tags(CacheTagHelper::brands())->remember($cacheKey, $ttl, $callback);
        }

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Get complete navigation menu structure.
     */
}
