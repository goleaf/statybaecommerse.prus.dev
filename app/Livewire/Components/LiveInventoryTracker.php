<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
/**
 * LiveInventoryTracker
 * 
 * Livewire component for LiveInventoryTracker with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property bool $autoRefresh
 * @property int $refreshInterval
 * @property array $selectedCategories
 * @property array $selectedBrands
 * @property string $stockFilter
 * @property string $sortBy
 * @property bool $showOnlyLowStock
 * @property int $lowStockThreshold
 */
final class LiveInventoryTracker extends Component
{
    public bool $autoRefresh = true;
    public int $refreshInterval = 60;
    // seconds
    public array $selectedCategories = [];
    public array $selectedBrands = [];
    public string $stockFilter = 'all';
    // all, low, out, in_stock
    public string $sortBy = 'stock_quantity';
    // stock_quantity, name, last_updated
    public bool $showOnlyLowStock = false;
    public int $lowStockThreshold = 10;
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        if ($this->autoRefresh) {
            $this->dispatch('start-inventory-refresh', interval: $this->refreshInterval * 1000);
        }
    }
    /**
     * Handle toggleAutoRefresh functionality with proper error handling.
     * @return void
     */
    public function toggleAutoRefresh(): void
    {
        $this->autoRefresh = !$this->autoRefresh;
        if ($this->autoRefresh) {
            $this->dispatch('start-inventory-refresh', interval: $this->refreshInterval * 1000);
        } else {
            $this->dispatch('stop-inventory-refresh');
        }
    }
    /**
     * Handle updateStockFilter functionality with proper error handling.
     * @param string $filter
     * @return void
     */
    public function updateStockFilter(string $filter): void
    {
        $this->stockFilter = $filter;
    }
    /**
     * Handle updateSortBy functionality with proper error handling.
     * @param string $sort
     * @return void
     */
    public function updateSortBy(string $sort): void
    {
        $this->sortBy = $sort;
    }
    /**
     * Handle toggleCategory functionality with proper error handling.
     * @param int $categoryId
     * @return void
     */
    public function toggleCategory(int $categoryId): void
    {
        if (in_array($categoryId, $this->selectedCategories)) {
            $this->selectedCategories = array_filter($this->selectedCategories, fn($id) => $id !== $categoryId);
        } else {
            $this->selectedCategories[] = $categoryId;
        }
    }
    /**
     * Handle toggleBrand functionality with proper error handling.
     * @param int $brandId
     * @return void
     */
    public function toggleBrand(int $brandId): void
    {
        if (in_array($brandId, $this->selectedBrands)) {
            $this->selectedBrands = array_filter($this->selectedBrands, fn($id) => $id !== $brandId);
        } else {
            $this->selectedBrands[] = $brandId;
        }
    }
    /**
     * Handle updateLowStockThreshold functionality with proper error handling.
     * @param int $threshold
     * @return void
     */
    public function updateLowStockThreshold(int $threshold): void
    {
        $this->lowStockThreshold = $threshold;
    }
    /**
     * Handle inventoryStats functionality with proper error handling.
     * @return array
     */
    #[Computed(persist: true, seconds: 120)]
    public function inventoryStats(): array
    {
        $cacheKey = "live_inventory_stats_{$this->stockFilter}_{$this->lowStockThreshold}";
        return Cache::remember($cacheKey, 120, function () {
            $query = Product::where('is_visible', true);
            // Apply filters
            if (!empty($this->selectedCategories)) {
                $query->whereIn('category_id', $this->selectedCategories);
            }
            if (!empty($this->selectedBrands)) {
                $query->whereIn('brand_id', $this->selectedBrands);
            }
            $totalProducts = $query->count();
            $inStockProducts = $query->where('stock_quantity', '>', 0)->count();
            $lowStockProducts = $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', $this->lowStockThreshold)->count();
            $outOfStockProducts = $query->where('stock_quantity', '<=', 0)->count();
            $totalStockValue = $query->sum(\DB::raw('stock_quantity * price'));
            $lowStockValue = $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', $this->lowStockThreshold)->sum(\DB::raw('stock_quantity * price'));
            return ['total_products' => $totalProducts, 'in_stock' => $inStockProducts, 'low_stock' => $lowStockProducts, 'out_of_stock' => $outOfStockProducts, 'total_stock_value' => $totalStockValue, 'low_stock_value' => $lowStockValue, 'stock_health_percentage' => $totalProducts > 0 ? round($inStockProducts / $totalProducts * 100, 1) : 0];
        });
    }
    /**
     * Handle inventoryItems functionality with proper error handling.
     * @return array
     */
    #[Computed(persist: true, seconds: 180)]
    public function inventoryItems(): array
    {
        $cacheKey = "live_inventory_items_{$this->stockFilter}_{$this->sortBy}_{$this->lowStockThreshold}_" . implode(',', $this->selectedCategories) . '_' . implode(',', $this->selectedBrands);
        return Cache::remember($cacheKey, 180, function () {
            $query = Product::with(['brand', 'categories', 'media'])->where('is_visible', true);
            // Apply category filter
            if (!empty($this->selectedCategories)) {
                $query->whereIn('category_id', $this->selectedCategories);
            }
            // Apply brand filter
            if (!empty($this->selectedBrands)) {
                $query->whereIn('brand_id', $this->selectedBrands);
            }
            // Apply stock filter
            match ($this->stockFilter) {
                'low' => $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', $this->lowStockThreshold),
                'out' => $query->where('stock_quantity', '<=', 0),
                'in_stock' => $query->where('stock_quantity', '>', 0),
                default => null,
            };
            // Apply sorting
            match ($this->sortBy) {
                'name' => $query->orderBy('name'),
                'last_updated' => $query->orderBy('updated_at', 'desc'),
                default => $query->orderBy('stock_quantity'),
            };
            return $query->limit(50)->get()->skipWhile(function ($product) {
                // Skip products that are not properly configured for inventory tracking
                return empty($product->name) || !$product->is_visible || empty($product->sku) || $product->price <= 0;
            })->map(function ($product) {
                return ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku, 'brand' => $product->brand?->name, 'category' => $product->categories->first()?->name, 'stock_quantity' => $product->stock_quantity, 'price' => $product->price, 'total_value' => $product->stock_quantity * $product->price, 'image' => $product->getFirstMediaUrl('images', 'thumb'), 'last_updated' => $product->updated_at, 'stock_status' => $this->getStockStatus($product->stock_quantity), 'stock_percentage' => $this->getStockPercentage($product->stock_quantity)];
            })->toArray();
        });
    }
    /**
     * Handle lowStockAlerts functionality with proper error handling.
     * @return array
     */
    #[Computed(persist: true, seconds: 300)]
    public function lowStockAlerts(): array
    {
        $cacheKey = "live_low_stock_alerts_{$this->lowStockThreshold}";
        return Cache::remember($cacheKey, 300, function () {
            return Product::with(['brand', 'categories'])->where('is_visible', true)->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', $this->lowStockThreshold)->orderBy('stock_quantity')->limit(20)->get()->skipWhile(function ($product) {
                // Skip products that are not properly configured for low stock alerts
                return empty($product->name) || !$product->is_visible || empty($product->sku) || $product->price <= 0 || $product->stock_quantity <= 0;
            })->map(function ($product) {
                return ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku, 'brand' => $product->brand?->name, 'stock_quantity' => $product->stock_quantity, 'threshold' => $this->lowStockThreshold, 'urgency' => $this->getUrgencyLevel($product->stock_quantity), 'last_updated' => $product->updated_at];
            })->toArray();
        });
    }
    /**
     * Handle inventoryCategories functionality with proper error handling.
     * @return array
     */
    #[Computed(persist: true, seconds: 600)]
    public function inventoryCategories(): array
    {
        return Cache::remember('live_inventory_categories', 600, function () {
            return \App\Models\Category::where('is_visible', true)->withCount(['products' => function ($query) {
                $query->where('is_visible', true);
            }])->orderBy('name')->get()->map(function ($category) {
                return ['id' => $category->id, 'name' => $category->name, 'products_count' => $category->products_count, 'selected' => in_array($category->id, $this->selectedCategories)];
            })->toArray();
        });
    }
    /**
     * Handle inventoryBrands functionality with proper error handling.
     * @return array
     */
    #[Computed(persist: true, seconds: 600)]
    public function inventoryBrands(): array
    {
        return Cache::remember('live_inventory_brands', 600, function () {
            return \App\Models\Brand::where('is_enabled', true)->withCount(['products' => function ($query) {
                $query->where('is_visible', true);
            }])->orderBy('name')->get()->map(function ($brand) {
                return ['id' => $brand->id, 'name' => $brand->name, 'products_count' => $brand->products_count, 'selected' => in_array($brand->id, $this->selectedBrands)];
            })->toArray();
        });
    }
    /**
     * Handle refreshInventory functionality with proper error handling.
     * @return void
     */
    #[On('refresh-inventory')]
    public function refreshInventory(): void
    {
        $this->clearCache();
        $this->dispatch('inventory-refreshed');
    }
    /**
     * Handle updateStock functionality with proper error handling.
     * @param int $productId
     * @param int $newQuantity
     * @return void
     */
    #[On('update-stock')]
    public function updateStock(int $productId, int $newQuantity): void
    {
        $product = Product::find($productId);
        if ($product) {
            $product->update(['stock_quantity' => $newQuantity]);
            $this->clearCache();
            $this->dispatch('stock-updated', productId: $productId, newQuantity: $newQuantity);
        }
    }
    /**
     * Handle getStockStatus functionality with proper error handling.
     * @param int $quantity
     * @return string
     */
    private function getStockStatus(int $quantity): string
    {
        if ($quantity <= 0) {
            return 'out_of_stock';
        }
        if ($quantity <= $this->lowStockThreshold) {
            return 'low_stock';
        }
        return 'in_stock';
    }
    /**
     * Handle getStockPercentage functionality with proper error handling.
     * @param int $quantity
     * @return float
     */
    private function getStockPercentage(int $quantity): float
    {
        // Calculate percentage based on a reasonable maximum stock level
        $maxStock = 100;
        // Adjust based on your business needs
        return min(100, $quantity / $maxStock * 100);
    }
    /**
     * Handle getUrgencyLevel functionality with proper error handling.
     * @param int $quantity
     * @return string
     */
    private function getUrgencyLevel(int $quantity): string
    {
        if ($quantity <= 0) {
            return 'critical';
        }
        if ($quantity <= 3) {
            return 'high';
        }
        if ($quantity <= $this->lowStockThreshold) {
            return 'medium';
        }
        return 'low';
    }
    /**
     * Handle clearCache functionality with proper error handling.
     * @return void
     */
    private function clearCache(): void
    {
        Cache::forget("live_inventory_stats_{$this->stockFilter}_{$this->lowStockThreshold}");
        Cache::forget("live_inventory_items_{$this->stockFilter}_{$this->sortBy}_{$this->lowStockThreshold}_" . implode(',', $this->selectedCategories) . '_' . implode(',', $this->selectedBrands));
        Cache::forget("live_low_stock_alerts_{$this->lowStockThreshold}");
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.live-inventory-tracker');
    }
}