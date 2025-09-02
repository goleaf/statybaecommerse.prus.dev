<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class ProductCatalog extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public array $categories = [];

    #[Url]
    public array $brands = [];

    #[Url]
    public string $sortBy = 'name';

    #[Url]
    public string $sortDirection = 'asc';

    #[Url]
    public int $minPrice = 0;

    #[Url]
    public int $maxPrice = 10000;

    public bool $showFilters = false;

    public function mount(): void
    {
        // Initialize default values
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategories(): void
    {
        $this->resetPage();
    }

    public function updatingBrands(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'categories', 'brands', 'minPrice', 'maxPrice']);
        $this->resetPage();
    }

    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }

    public function getProductsProperty()
    {
        return Product::query()
            ->with(['brand', 'categories', 'media'])
            ->where('is_visible', true)
            ->where('status', 'published')
            ->where(function (Builder $query) {
                if ($this->search) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%');
                }
            })
            ->when($this->categories, function (Builder $query) {
                $query->whereHas('categories', function (Builder $q) {
                    $q->whereIn('categories.id', $this->categories);
                });
            })
            ->when($this->brands, function (Builder $query) {
                $query->whereIn('brand_id', $this->brands);
            })
            ->whereBetween('price', [$this->minPrice, $this->maxPrice])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(12);
    }

    public function getCategoriesProperty()
    {
        return Category::query()
            ->where('is_visible', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }

    public function getBrandsProperty()
    {
        return Brand::query()
            ->where('is_enabled', true)
            ->withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pages.product-catalog', [
            'products' => $this->products,
            'availableCategories' => $this->categories,
            'availableBrands' => $this->brands,
        ]);
    }
}
