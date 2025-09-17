<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
/**
 * ProductFilterWidget
 * 
 * Livewire component for ProductFilterWidget with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property string $search
 * @property array $categories
 * @property array $brands
 * @property array $selectedAttributes
 * @property float $minPrice
 * @property float $maxPrice
 * @property bool $inStock
 * @property bool $onSale
 * @property string $sortBy
 * @property string $sortDirection
 */
final class ProductFilterWidget extends Component
{
    #[Url]
    public string $search = '';
    #[Url]
    public array $categories = [];
    #[Url]
    public array $brands = [];
    #[Url]
    public array $selectedAttributes = [];
    #[Url]
    public float $minPrice = 0;
    #[Url]
    public float $maxPrice = 10000;
    #[Url]
    public bool $inStock = false;
    #[Url]
    public bool $onSale = false;
    #[Url]
    public string $sortBy = 'created_at';
    #[Url]
    public string $sortDirection = 'desc';
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->updatePriceRange();
    }
    /**
     * Handle updatedSearch functionality with proper error handling.
     * @return void
     */
    public function updatedSearch(): void
    {
        $this->dispatch('filter-updated');
    }
    /**
     * Handle updatedCategories functionality with proper error handling.
     * @return void
     */
    public function updatedCategories(): void
    {
        $this->dispatch('filter-updated');
    }
    /**
     * Handle updatedBrands functionality with proper error handling.
     * @return void
     */
    public function updatedBrands(): void
    {
        $this->dispatch('filter-updated');
    }
    /**
     * Handle updatedSelectedAttributes functionality with proper error handling.
     * @return void
     */
    public function updatedSelectedAttributes(): void
    {
        $this->dispatch('filter-updated');
    }
    /**
     * Handle updatedMinPrice functionality with proper error handling.
     * @return void
     */
    public function updatedMinPrice(): void
    {
        $this->dispatch('filter-updated');
    }
    /**
     * Handle updatedMaxPrice functionality with proper error handling.
     * @return void
     */
    public function updatedMaxPrice(): void
    {
        $this->dispatch('filter-updated');
    }
    /**
     * Handle updatedInStock functionality with proper error handling.
     * @return void
     */
    public function updatedInStock(): void
    {
        $this->dispatch('filter-updated');
    }
    /**
     * Handle updatedOnSale functionality with proper error handling.
     * @return void
     */
    public function updatedOnSale(): void
    {
        $this->dispatch('filter-updated');
    }
    /**
     * Handle updatedSortBy functionality with proper error handling.
     * @return void
     */
    public function updatedSortBy(): void
    {
        $this->dispatch('filter-updated');
    }
    /**
     * Handle updatedSortDirection functionality with proper error handling.
     * @return void
     */
    public function updatedSortDirection(): void
    {
        $this->dispatch('filter-updated');
    }
    /**
     * Handle clearFilters functionality with proper error handling.
     * @return void
     */
    public function clearFilters(): void
    {
        $this->reset(['search', 'categories', 'brands', 'attributes', 'inStock', 'onSale', 'sortBy', 'sortDirection']);
        $this->updatePriceRange();
        $this->dispatch('filter-updated');
    }
    /**
     * Handle updatePriceRange functionality with proper error handling.
     * @return void
     */
    public function updatePriceRange(): void
    {
        $priceRange = Product::where('is_visible', true)->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        $this->minPrice = $priceRange->min_price ?? 0;
        $this->maxPrice = $priceRange->max_price ?? 10000;
    }
    /**
     * Handle availableCategories functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function availableCategories(): Collection
    {
        return Category::where('is_visible', true)->with(['translations' => fn($q) => $q->where('locale', app()->getLocale())])->orderBy('name')->get();
    }
    /**
     * Handle availableBrands functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function availableBrands(): Collection
    {
        return Brand::with(['translations' => fn($q) => $q->where('locale', app()->getLocale())])->orderBy('name')->get();
    }
    /**
     * Handle availableAttributes functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function availableAttributes(): Collection
    {
        return Attribute::with(['values' => function ($query) {
            $query->with(['translations' => fn($q) => $q->where('locale', app()->getLocale())])->orderBy('sort_order');
        }])->with(['translations' => fn($q) => $q->where('locale', app()->getLocale())])->where('is_filterable', true)->orderBy('sort_order')->get();
    }
    /**
     * Handle getFilteredProductsQuery functionality with proper error handling.
     */
    public function getFilteredProductsQuery()
    {
        $query = Product::query()->with(['brand', 'categories', 'media', 'translations'])->where('is_visible', true);
        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')->orWhere('description', 'like', '%' . $this->search . '%')->orWhere('sku', 'like', '%' . $this->search . '%')->orWhereHas('brand', function ($brandQuery) {
                    $brandQuery->where('name', 'like', '%' . $this->search . '%');
                });
            });
        }
        // Category filter
        if (!empty($this->categories)) {
            $query->whereHas('categories', function ($q) {
                $q->whereIn('categories.id', $this->categories);
            });
        }
        // Brand filter
        if (!empty($this->brands)) {
            $query->whereIn('brand_id', $this->brands);
        }
        // Price range filter
        if ($this->minPrice > 0 || $this->maxPrice < 10000) {
            $query->whereBetween('price', [$this->minPrice, $this->maxPrice]);
        }
        // Stock filter
        if ($this->inStock) {
            $query->where('stock_quantity', '>', 0);
        }
        // Sale filter
        if ($this->onSale) {
            $query->whereNotNull('sale_price');
        }
        // Attribute filters
        if (!empty($this->selectedAttributes)) {
            foreach ($this->selectedAttributes as $attributeId => $valueIds) {
                if (!empty($valueIds)) {
                    $query->whereHas('attributes', function ($q) use ($attributeId, $valueIds) {
                        $q->where('attributes.id', $attributeId)->whereHas('values', function ($valueQuery) use ($valueIds) {
                            $valueQuery->whereIn('attribute_values.id', $valueIds);
                        });
                    });
                }
            }
        }
        // Sorting
        $query->orderBy($this->sortBy, $this->sortDirection);
        return $query;
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.product-filter', ['availableCategories' => $this->availableCategories, 'availableBrands' => $this->availableBrands, 'availableAttributes' => $this->availableAttributes]);
    }
}
