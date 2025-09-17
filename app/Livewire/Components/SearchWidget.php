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
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
/**
 * SearchWidget
 * 
 * Livewire component for SearchWidget with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property string $query
 * @property array $selectedCategories
 * @property array $selectedBrands
 * @property array $selectedAttributes
 * @property float|null $minPrice
 * @property float|null $maxPrice
 * @property string $sortBy
 * @property string $sortDirection
 * @property bool $inStock
 * @property bool $onSale
 * @property string $viewMode
 * @property int $perPage
 * @property mixed $queryString
 */
final class SearchWidget extends Component
{
    use WithPagination;
    #[Validate('nullable|string|max:255')]
    public string $query = '';
    public array $selectedCategories = [];
    public array $selectedBrands = [];
    public array $selectedAttributes = [];
    public ?float $minPrice = null;
    public ?float $maxPrice = null;
    public string $sortBy = 'relevance';
    public string $sortDirection = 'desc';
    public bool $inStock = false;
    public bool $onSale = false;
    public string $viewMode = 'grid';
    // grid, list
    public int $perPage = 12;
    protected $queryString = ['query' => ['except' => ''], 'selectedCategories' => ['except' => []], 'selectedBrands' => ['except' => []], 'selectedAttributes' => ['except' => []], 'minPrice' => ['except' => null], 'maxPrice' => ['except' => null], 'sortBy' => ['except' => 'relevance'], 'inStock' => ['except' => false], 'onSale' => ['except' => false], 'viewMode' => ['except' => 'grid']];
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->query = request('q', '');
    }
    /**
     * Handle updatedQuery functionality with proper error handling.
     * @return void
     */
    public function updatedQuery(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedSelectedCategories functionality with proper error handling.
     * @return void
     */
    public function updatedSelectedCategories(): void
    {
        $this->resetPage();
    }
    /**
     * Handle updatedSelectedBrands functionality with proper error handling.
     * @return void
     */
    public function updatedSelectedBrands(): void
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
     * Handle clearFilters functionality with proper error handling.
     * @return void
     */
    public function clearFilters(): void
    {
        $this->reset(['selectedCategories', 'selectedBrands', 'selectedAttributes', 'minPrice', 'maxPrice', 'inStock', 'onSale']);
        $this->resetPage();
    }
    /**
     * Handle toggleCategory functionality with proper error handling.
     * @param int $categoryId
     * @return void
     */
    public function toggleCategory(int $categoryId): void
    {
        if (in_array($categoryId, $this->selectedCategories)) {
            $this->selectedCategories = array_diff($this->selectedCategories, [$categoryId]);
        } else {
            $this->selectedCategories[] = $categoryId;
        }
        $this->resetPage();
    }
    /**
     * Handle toggleBrand functionality with proper error handling.
     * @param int $brandId
     * @return void
     */
    public function toggleBrand(int $brandId): void
    {
        if (in_array($brandId, $this->selectedBrands)) {
            $this->selectedBrands = array_diff($this->selectedBrands, [$brandId]);
        } else {
            $this->selectedBrands[] = $brandId;
        }
        $this->resetPage();
    }
    /**
     * Handle toggleAttribute functionality with proper error handling.
     * @param int $attributeValueId
     * @return void
     */
    public function toggleAttribute(int $attributeValueId): void
    {
        if (in_array($attributeValueId, $this->selectedAttributes)) {
            $this->selectedAttributes = array_diff($this->selectedAttributes, [$attributeValueId]);
        } else {
            $this->selectedAttributes[] = $attributeValueId;
        }
        $this->resetPage();
    }
    /**
     * Handle products functionality with proper error handling.
     * @return LengthAwarePaginator
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $query = Product::query()->with(['brand', 'categories', 'media', 'variants', 'reviews'])->where('is_visible', true);
        // Text search
        if ($this->query) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->query . '%')->orWhere('description', 'like', '%' . $this->query . '%')->orWhere('sku', 'like', '%' . $this->query . '%')->orWhereHas('brand', function ($brandQuery) {
                    $brandQuery->where('name', 'like', '%' . $this->query . '%');
                })->orWhereHas('categories', function ($catQuery) {
                    $catQuery->where('name', 'like', '%' . $this->query . '%');
                });
            });
        }
        // Category filter
        if (!empty($this->selectedCategories)) {
            $query->whereHas('categories', function ($q) {
                $q->whereIn('categories.id', $this->selectedCategories);
            });
        }
        // Brand filter
        if (!empty($this->selectedBrands)) {
            $query->whereIn('brand_id', $this->selectedBrands);
        }
        // Attribute filter
        if (!empty($this->selectedAttributes)) {
            $query->whereHas('variants.attributeValues', function ($q) {
                $q->whereIn('attribute_values.id', $this->selectedAttributes);
            });
        }
        // Price range filter
        if ($this->minPrice !== null) {
            $query->whereHas('variants', function ($q) {
                $q->where('price', '>=', $this->minPrice);
            });
        }
        if ($this->maxPrice !== null) {
            $query->whereHas('variants', function ($q) {
                $q->where('price', '<=', $this->maxPrice);
            });
        }
        // Stock filter
        if ($this->inStock) {
            $query->whereHas('variants', function ($q) {
                $q->where('stock_quantity', '>', 0);
            });
        }
        // Sale filter
        if ($this->onSale) {
            $query->whereHas('variants', function ($q) {
                $q->whereNotNull('compare_price')->whereColumn('compare_price', '>', 'price');
            });
        }
        // Sorting
        switch ($this->sortBy) {
            case 'price_asc':
                $query->join('product_variants', 'products.id', '=', 'product_variants.product_id')->orderBy('product_variants.price', 'asc');
                break;
            case 'price_desc':
                $query->join('product_variants', 'products.id', '=', 'product_variants.product_id')->orderBy('product_variants.price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', $this->sortDirection);
                break;
            case 'created_at':
                $query->orderBy('created_at', $this->sortDirection);
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', 'desc');
                break;
            default:
                // relevance
                if ($this->query) {
                    // Boost exact matches
                    $query->orderByRaw('CASE 
                        WHEN name LIKE ? THEN 1
                        WHEN name LIKE ? THEN 2
                        WHEN description LIKE ? THEN 3
                        ELSE 4
                    END', [$this->query, '%' . $this->query . '%', '%' . $this->query . '%']);
                } else {
                    $query->orderBy('created_at', 'desc');
                }
                break;
        }
        return $query->distinct()->paginate($this->perPage);
    }
    /**
     * Handle categories functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::where('is_visible', true)->whereHas('products')->withCount('products')->orderBy('name')->get();
    }
    /**
     * Handle brands functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function brands(): Collection
    {
        return Brand::whereHas('products')->withCount('products')->orderBy('name')->get();
    }
    /**
     * Handle attributes functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function attributes(): Collection
    {
        return Attribute::with(['values' => function ($query) {
            $query->whereHas('productVariants.product', function ($q) {
                $q->where('is_visible', true);
            });
        }])->whereHas('values.productVariants.product', function ($q) {
            $q->where('is_visible', true);
        })->orderBy('name')->get();
    }
    /**
     * Handle priceRange functionality with proper error handling.
     * @return array
     */
    #[Computed]
    public function priceRange(): array
    {
        $prices = ProductVariant::whereHas('product', function ($q) {
            $q->where('is_visible', true);
        })->pluck('price');
        return ['min' => $prices->min() ?? 0, 'max' => $prices->max() ?? 1000];
    }
    /**
     * Handle activeFiltersCount functionality with proper error handling.
     * @return int
     */
    #[Computed]
    public function activeFiltersCount(): int
    {
        return count($this->selectedCategories) + count($this->selectedBrands) + count($this->selectedAttributes) + ($this->minPrice ? 1 : 0) + ($this->maxPrice ? 1 : 0) + ($this->inStock ? 1 : 0) + ($this->onSale ? 1 : 0);
    }
    /**
     * Handle checkWishlistStatus functionality with proper error handling.
     * @return void
     */
    private function checkWishlistStatus(): void
    {
        if (auth()->check()) {
            $this->isWishlisted = auth()->user()->wishlist()->where('product_id', $this->product->id)->exists();
        }
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.search', ['products' => $this->products, 'categories' => $this->categories, 'brands' => $this->brands, 'attributes' => $this->attributes, 'priceRange' => $this->priceRange, 'activeFiltersCount' => $this->activeFiltersCount]);
    }
}
