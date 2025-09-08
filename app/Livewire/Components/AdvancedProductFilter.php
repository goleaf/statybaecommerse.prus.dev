<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Attributes\Url;

final class AdvancedProductFilter extends Component
{
    #[Url]
    public string $search = '';
    
    #[Url]
    public array $categories = [];
    
    #[Url]
    public array $brands = [];
    
    #[Url]
    public array $attributes = [];
    
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

    public function mount(): void
    {
        $this->updatePriceRange();
    }

    public function updatedSearch(): void
    {
        $this->dispatch('filter-updated');
    }

    public function updatedCategories(): void
    {
        $this->dispatch('filter-updated');
    }

    public function updatedBrands(): void
    {
        $this->dispatch('filter-updated');
    }

    public function updatedAttributes(): void
    {
        $this->dispatch('filter-updated');
    }

    public function updatedMinPrice(): void
    {
        $this->dispatch('filter-updated');
    }

    public function updatedMaxPrice(): void
    {
        $this->dispatch('filter-updated');
    }

    public function updatedInStock(): void
    {
        $this->dispatch('filter-updated');
    }

    public function updatedOnSale(): void
    {
        $this->dispatch('filter-updated');
    }

    public function updatedSortBy(): void
    {
        $this->dispatch('filter-updated');
    }

    public function updatedSortDirection(): void
    {
        $this->dispatch('filter-updated');
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search', 'categories', 'brands', 'attributes', 
            'inStock', 'onSale', 'sortBy', 'sortDirection'
        ]);
        $this->updatePriceRange();
        $this->dispatch('filter-updated');
    }

    public function updatePriceRange(): void
    {
        $priceRange = Product::where('is_visible', true)
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();
            
        $this->minPrice = $priceRange->min_price ?? 0;
        $this->maxPrice = $priceRange->max_price ?? 10000;
    }

    public function getAvailableCategoriesProperty()
    {
        return Category::where('is_visible', true)
            ->withTranslation()
            ->orderBy('name')
            ->get();
    }

    public function getAvailableBrandsProperty()
    {
        return Brand::where('is_visible', true)
            ->withTranslation()
            ->orderBy('name')
            ->get();
    }

    public function getAvailableAttributesProperty()
    {
        return Attribute::with(['values' => function ($query) {
            $query->withTranslation()->orderBy('sort_order');
        }])
        ->withTranslation()
        ->where('is_filterable', true)
        ->orderBy('sort_order')
        ->get();
    }

    public function getFilteredProductsQuery()
    {
        $query = Product::query()
            ->with(['brand', 'categories', 'media', 'translations'])
            ->where('is_visible', true);

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%')
                  ->orWhereHas('brand', function ($brandQuery) {
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
        if (!empty($this->attributes)) {
            foreach ($this->attributes as $attributeId => $valueIds) {
                if (!empty($valueIds)) {
                    $query->whereHas('attributes', function ($q) use ($attributeId, $valueIds) {
                        $q->where('attributes.id', $attributeId)
                          ->whereHas('values', function ($valueQuery) use ($valueIds) {
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

    public function render(): View
    {
        return view('livewire.components.advanced-product-filter', [
            'availableCategories' => $this->availableCategories,
            'availableBrands' => $this->availableBrands,
            'availableAttributes' => $this->availableAttributes,
        ]);
    }
}
