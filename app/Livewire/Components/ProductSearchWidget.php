<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
/**
 * ProductSearchWidget
 * 
 * Livewire component for ProductSearchWidget with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property string $search
 * @property array $categories
 * @property array $brands
 * @property array $selectedAttributes
 * @property float|null $minPrice
 * @property float|null $maxPrice
 * @property string $sortBy
 * @property string $sortDirection
 * @property bool $inStock
 * @property bool $onSale
 * @property bool $featured
 * @property string $viewMode
 * @property int $perPage
 * @property bool $showFilters
 */
final class ProductSearchWidget extends Component
{
    use WithPagination;
    #[Url(except: '')]
    public string $search = '';
    #[Url(except: [])]
    public array $categories = [];
    #[Url(except: [])]
    public array $brands = [];
    #[Url(except: [])]
    public array $selectedAttributes = [];
    #[Url(except: null)]
    public ?float $minPrice = null;
    #[Url(except: null)]
    public ?float $maxPrice = null;
    #[Url(except: 'relevance')]
    public string $sortBy = 'relevance';
    #[Url(except: 'desc')]
    public string $sortDirection = 'desc';
    #[Url(except: false)]
    public bool $inStock = false;
    #[Url(except: false)]
    public bool $onSale = false;
    #[Url(except: false)]
    public bool $featured = false;
    #[Url(except: 'grid')]
    public string $viewMode = 'grid';
    public int $perPage = 12;
    public bool $showFilters = false;
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        // Set initial price range if not set
        if ($this->minPrice === null || $this->maxPrice === null) {
            $priceRange = Product::selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
            $this->minPrice = $this->minPrice ?? $priceRange->min_price ?? 0;
            $this->maxPrice = $this->maxPrice ?? $priceRange->max_price ?? 1000;
        }
    }
    /**
     * Handle updatedSearch functionality with proper error handling.
     * @return void
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedCategories functionality with proper error handling.
     * @return void
     */
    public function updatedCategories(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedBrands functionality with proper error handling.
     * @return void
     */
    public function updatedBrands(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedSelectedAttributes functionality with proper error handling.
     * @return void
     */
    public function updatedSelectedAttributes(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedMinPrice functionality with proper error handling.
     * @return void
     */
    public function updatedMinPrice(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedMaxPrice functionality with proper error handling.
     * @return void
     */
    public function updatedMaxPrice(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedSortBy functionality with proper error handling.
     * @return void
     */
    public function updatedSortBy(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedInStock functionality with proper error handling.
     * @return void
     */
    public function updatedInStock(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedOnSale functionality with proper error handling.
     * @return void
     */
    public function updatedOnSale(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedFeatured functionality with proper error handling.
     * @return void
     */
    public function updatedFeatured(): void
    {
        $this->resetPage();
    }
    /**
     * Handle clearFilters functionality with proper error handling.
     * @return void
     */
    public function clearFilters(): void
    {
        $this->reset(['search', 'categories', 'brands', 'selectedAttributes', 'minPrice', 'maxPrice', 'inStock', 'onSale', 'featured']);
        $this->sortBy = 'relevance';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }
    /**
     * Handle toggleFilters functionality with proper error handling.
     * @return void
     */
    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }
    /**
     * Handle products functionality with proper error handling.
     * @return LengthAwarePaginator
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $query = Product::query()->with(['media', 'brand', 'categories', 'variants'])->where('is_visible', true);
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
            $query->whereHas('categories', function ($categoryQuery) {
                $categoryQuery->whereIn('categories.id', $this->categories);
            });
        }
        // Brand filter
        if (!empty($this->brands)) {
            $query->whereIn('brand_id', $this->brands);
        }
        // Attribute filter
        if (!empty($this->selectedAttributes)) {
            foreach ($this->selectedAttributes as $attributeId => $valueIds) {
                if (!empty($valueIds)) {
                    $query->whereHas('variants.attributeValues', function ($attrQuery) use ($valueIds) {
                        $attrQuery->whereIn('attribute_values.id', $valueIds);
                    });
                }
            }
        }
        // Price filter
        if ($this->minPrice !== null) {
            $query->where('price', '>=', $this->minPrice);
        }
        if ($this->maxPrice !== null) {
            $query->where('price', '<=', $this->maxPrice);
        }
        // Stock filter
        if ($this->inStock) {
            $query->where('stock_quantity', '>', 0);
        }
        // Sale filter
        if ($this->onSale) {
            $query->whereNotNull('sale_price')->where('sale_price', '<', \DB::raw('price'));
        }
        // Featured filter
        if ($this->featured) {
            $query->where('is_featured', true);
        }
        // Sorting
        match ($this->sortBy) {
            'name' => $query->orderBy('name', $this->sortDirection),
            'price' => $query->orderBy('price', $this->sortDirection),
            'created_at' => $query->orderBy('created_at', $this->sortDirection),
            'popularity' => $query->withCount('orderItems')->orderBy('order_items_count', $this->sortDirection),
            'rating' => $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', $this->sortDirection),
            default => $query->orderBy('created_at', 'desc'),
        };
        return $query->paginate($this->perPage);
    }
    /**
     * Handle getCategoriesProperty functionality with proper error handling.
     * @return Collection
     */
    public function getCategoriesProperty(): Collection
    {
        return Category::where('is_visible', true)->whereHas('products')->withCount('products')->orderBy('name')->get();
    }
    /**
     * Handle getBrandsProperty functionality with proper error handling.
     * @return Collection
     */
    public function getBrandsProperty(): Collection
    {
        return Brand::where('is_enabled', true)->whereHas('products')->withCount('products')->orderBy('name')->get();
    }
    /**
     * Handle getAttributesProperty functionality with proper error handling.
     * @return Collection
     */
    public function getAttributesProperty(): Collection
    {
        return Attribute::where('is_filterable', true)->with(['values' => function ($query) {
            $query->whereHas('products')->orderBy('name');
        }])->orderBy('name')->get();
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.advanced-product-search');
    }
}