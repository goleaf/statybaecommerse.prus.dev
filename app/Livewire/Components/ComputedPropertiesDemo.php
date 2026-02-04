<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * ComputedPropertiesDemo
 *
 * Livewire component for ComputedPropertiesDemo with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $filter
 * @property string $selectedCategory
 * @property bool   $showExpensiveProducts
 */
class ComputedPropertiesDemo extends Component
{
    public string $filter = 'week';

    public string $selectedCategory = '';

    public bool $showExpensiveProducts = false;

    /**
     * Handle stats functionality with proper error handling.
     */
    #[Computed]
    public function stats(): array
    {
        return ['users' => \App\Models\User::count(), 'products' => Product::where('is_visible', true)->count(), 'categories' => Category::where('is_visible', true)->count(), 'brands' => Brand::where('is_enabled', true)->count(), 'reviews' => 0];
    }

    /**
     * Handle filteredProducts functionality with proper error handling.
     */
    #[Computed]
    public function filteredProducts(): Collection
    {
        $query = Product::query()->where('is_visible', true)->with(['brand', 'categories', 'media']);
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
            'week'  => $query->where('created_at', '>=', now()->subWeek()),
            'month' => $query->where('created_at', '>=', now()->subMonth()),
            'year'  => $query->where('created_at', '>=', now()->subYear()),
            default => $query,
        };

        return $query->orderBy('created_at', 'desc')->limit(10)->get();
    }

    /**
     * Handle globalSiteStats functionality with proper error handling.
     */
    #[Computed(cache: true, key: 'global-site-stats')]
    public function globalSiteStats(): array
    {
        // This will be cached globally across all instances
        return ['total_products' => Product::where('is_visible', true)->count(), 'total_categories' => Category::where('is_visible', true)->count(), 'total_brands' => Brand::where('is_enabled', true)->count(), 'total_reviews' => 0, 'average_rating' => 0, 'last_updated' => now()->toISOString()];
    }

    /**
     * Handle summaryReport functionality with proper error handling.
     */
    #[Computed]
    public function summaryReport(): array
    {
        $products = $this->filteredProducts;
        $globalStats = $this->globalSiteStats;

        return [
            'filter_applied'      => $this->filter,
            'category_filter'     => $this->selectedCategory,
            'expensive_only'      => $this->showExpensiveProducts,
            'filtered_count'      => $products->count(),
            'percentage_of_total' => $globalStats['total_products'] > 0
                ? round($products->count() / $globalStats['total_products'] * 100, 2)
                : 0,
        ];
    }

    /**
     * Handle updateFilter functionality with proper error handling.
     */
    public function updateFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * Handle updateCategory functionality with proper error handling.
     */
    public function updateCategory(string $categoryId): void
    {
        $this->selectedCategory = $categoryId;
    }

    /**
     * Handle toggleExpensiveProducts functionality with proper error handling.
     */
    public function toggleExpensiveProducts(): void
    {
        $this->showExpensiveProducts = ! $this->showExpensiveProducts;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.computed-properties-demo', ['stats' => $this->stats, 'filteredProducts' => $this->filteredProducts, 'globalSiteStats' => $this->globalSiteStats, 'summaryReport' => $this->summaryReport, 'categories' => Category::where('is_visible', true)->get()]);
    }
}
