<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * ComputedPropertiesDemo
 * 
 * Livewire component demonstrating all computed property features from Laravel News article.
 */
class ComputedPropertiesDemo extends Component
{
    public string $filter = 'week';
    public string $selectedCategory = '';
    public bool $showExpensiveProducts = false;

    /**
     * Basic computed property - cached during single request lifecycle
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'users' => \App\Models\User::count(),
            'products' => Product::where('is_visible', true)->count(),
            'categories' => Category::where('is_visible', true)->count(),
            'brands' => Brand::where('is_enabled', true)->count(),
            'reviews' => Review::where('is_approved', true)->count(),
        ];
    }

    /**
     * Computed property that depends on component properties
     */
    #[Computed]
    public function filteredProducts(): Collection
    {
        $query = Product::query()
            ->where('is_visible', true)
            ->with(['brand', 'categories', 'media']);

        // Apply category filter
        if ($this->selectedCategory) {
            $query->whereHas('categories', function ($q) {
                $q->where('categories.id', $this->selectedCategory);
            });
        }

        // Apply price filter
        if ($this->showExpensiveProducts) {
            $query->where('price', '>', 100);
        }

        // Apply time filter
        match ($this->filter) {
            'week' => $query->where('created_at', '>=', now()->subWeek()),
            'month' => $query->where('created_at', '>=', now()->subMonth()),
            'year' => $query->where('created_at', '>=', now()->subYear()),
            default => $query,
        };

        return $query->orderBy('created_at', 'desc')->limit(10)->get();
    }

    /**
     * Computed property with complex calculations
     */
    #[Computed]
    public function analyticsData(): array
    {
        $products = $this->filteredProducts;
        
        return [
            'total_products' => $products->count(),
            'average_price' => $products->avg('price') ?? 0,
            'total_value' => $products->sum('price'),
            'price_range' => [
                'min' => $products->min('price') ?? 0,
                'max' => $products->max('price') ?? 0,
            ],
            'brand_distribution' => $products->groupBy('brand.name')->map->count(),
            'category_distribution' => $products->flatMap->categories->groupBy('name')->map->count(),
        ];
    }

    /**
     * Persistent computed property - cached across multiple requests
     */
    #[Computed(persist: true)]
    public function expensiveAnalytics(): array
    {
        // This expensive calculation will be cached across requests
        $topProducts = Product::query()
            ->where('is_visible', true)
            ->whereHas('reviews')
            ->withCount('reviews')
            ->orderByDesc('reviews_count')
            ->limit(5)
            ->get();

        $topBrands = Brand::query()
            ->where('is_enabled', true)
            ->whereHas('products', function ($query) {
                $query->where('is_visible', true);
            })
            ->withCount(['products' => function ($query) {
                $query->where('is_visible', true);
            }])
            ->orderByDesc('products_count')
            ->limit(5)
            ->get();

        return [
            'top_products' => $topProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'reviews_count' => $product->reviews_count,
                    'image' => $product->getFirstMediaUrl('images'),
                ];
            }),
            'top_brands' => $topBrands->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'products_count' => $brand->products_count,
                    'image' => $brand->getFirstMediaUrl('logo'),
                ];
            }),
        ];
    }

    /**
     * Global computed property - cached across all instances
     */
    #[Computed(cache: true, key: 'global-site-stats')]
    public function globalSiteStats(): array
    {
        // This will be cached globally across all instances
        return [
            'total_products' => Product::where('is_visible', true)->count(),
            'total_categories' => Category::where('is_visible', true)->count(),
            'total_brands' => Brand::where('is_enabled', true)->count(),
            'total_reviews' => Review::where('is_approved', true)->count(),
            'average_rating' => Review::where('is_approved', true)->avg('rating') ?? 0,
            'last_updated' => now()->toISOString(),
        ];
    }

    /**
     * Computed property that depends on other computed properties
     */
    #[Computed]
    public function summaryReport(): array
    {
        $stats = $this->stats;
        $analytics = $this->analyticsData;
        $globalStats = $this->globalSiteStats;

        return [
            'filter_applied' => $this->filter,
            'category_filter' => $this->selectedCategory,
            'expensive_only' => $this->showExpensiveProducts,
            'filtered_count' => $analytics['total_products'],
            'percentage_of_total' => $globalStats['total_products'] > 0 
                ? round(($analytics['total_products'] / $globalStats['total_products']) * 100, 2)
                : 0,
            'average_price_vs_global' => $analytics['average_price'],
            'performance_metrics' => [
                'cache_hits' => 'Computed properties are cached automatically',
                'database_queries_reduced' => 'Multiple calls to same computed property use cache',
                'memory_optimized' => 'Results stored in memory during request lifecycle',
            ],
        ];
    }

    public function updateFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function updateCategory(string $categoryId): void
    {
        $this->selectedCategory = $categoryId;
    }

    public function toggleExpensiveProducts(): void
    {
        $this->showExpensiveProducts = !$this->showExpensiveProducts;
    }

    public function render(): View
    {
        return view('livewire.components.computed-properties-demo', [
            'stats' => $this->stats,
            'filteredProducts' => $this->filteredProducts,
            'analyticsData' => $this->analyticsData,
            'expensiveAnalytics' => $this->expensiveAnalytics,
            'globalSiteStats' => $this->globalSiteStats,
            'summaryReport' => $this->summaryReport,
            'categories' => Category::where('is_visible', true)->get(),
        ]);
    }
}
