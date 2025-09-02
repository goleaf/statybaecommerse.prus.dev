<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithPagination;

final class ProductCatalog extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $categoryId = null;
    public ?int $brandId = null;
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    public function mount(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedBrandId(): void
    {
        $this->resetPage();
    }

    public function getProductsProperty()
    {
        return Product::query()
            ->with(['brand', 'category', 'media'])
            ->where('is_visible', true)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryId, fn($query) => $query->where('category_id', $this->categoryId))
            ->when($this->brandId, fn($query) => $query->where('brand_id', $this->brandId))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(12);
    }

    public function getCategoriesProperty(): Collection
    {
        return Category::where('is_visible', true)->orderBy('name')->get();
    }

    public function getBrandsProperty(): Collection
    {
        return Brand::where('is_visible', true)->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.pages.product-catalog', [
            'products' => $this->products,
            'categories' => $this->categories,
            'brands' => $this->brands,
        ])->layout('components.layouts.base', [
            'title' => __('Products')
        ]);
    }
}