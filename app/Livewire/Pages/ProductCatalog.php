<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class ProductCatalog extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public array $selectedCategories = [];

    #[Url]
    public array $selectedBrands = [];

    #[Url]
    public string $sortBy = 'name';

    #[Url]
    public string $sortDirection = 'asc';

    #[Url]
    public int $priceMin = 0;

    #[Url]
    public int $priceMax = 10000;

    #[Url]
    public string $availability = 'all';

    #[Url]
    public int $perPage = 12;

    public bool $showFilters = false;

    public function mount(): void
    {
        $this->priceMax = (int) Product::max('price') ?: 10000;
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        return Product::query()
            ->with(['brand', 'categories', 'media', 'variants'])
            ->where('is_visible', true)
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $subQuery) {
                    $subQuery
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhereHas('brand', function (Builder $brandQuery) {
                            $brandQuery->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->selectedCategories, function (Builder $query) {
                $query->whereHas('categories', function (Builder $categoryQuery) {
                    $categoryQuery->whereIn('categories.id', $this->selectedCategories);
                });
            })
            ->when($this->selectedBrands, function (Builder $query) {
                $query->whereIn('brand_id', $this->selectedBrands);
            })
            ->when($this->priceMin > 0, function (Builder $query) {
                $query->where('price', '>=', $this->priceMin);
            })
            ->when($this->priceMax < 10000, function (Builder $query) {
                $query->where('price', '<=', $this->priceMax);
            })
            ->when($this->availability === 'in_stock', function (Builder $query) {
                $query->where('stock_quantity', '>', 0);
            })
            ->when($this->availability === 'out_of_stock', function (Builder $query) {
                $query->where('stock_quantity', '<=', 0);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->withCount(['products' => function (Builder $query) {
                $query->where('is_visible', true);
            }])
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function brands()
    {
        return Brand::query()
            ->withCount(['products' => function (Builder $query) {
                $query->where('is_visible', true);
            }])
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedCategories(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedBrands(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
    }

    public function updatedPriceMin(): void
    {
        $this->resetPage();
    }

    public function updatedPriceMax(): void
    {
        $this->resetPage();
    }

    public function updatedAvailability(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'selectedCategories',
            'selectedBrands',
            'priceMin',
            'priceMax',
            'availability',
        ]);
        $this->priceMax = (int) Product::max('price') ?: 10000;
        $this->resetPage();
    }

    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }

    public function addToCart(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $cartItems = session()->get('cart', []);

        if (isset($cartItems[$productId])) {
            $cartItems[$productId]['quantity']++;
        } else {
            $cartItems[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image' => $product->getFirstMediaUrl('images'),
            ];
        }

        session()->put('cart', $cartItems);

        $this->dispatch('cart-updated');
        $this->dispatch('notify',
            message: __('Product added to cart'),
            type: 'success');
    }

    public function render()
    {
        return view('livewire.pages.product-catalog', [
            'products' => $this->products,
            'categories' => $this->categories,
            'brands' => $this->brands,
        ])->title(__('Products'));
    }
}
