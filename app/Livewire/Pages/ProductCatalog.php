<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Livewire\Concerns\WithFilters;
use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

final class ProductCatalog extends Component
{
    use WithFilters, WithCart, WithNotifications;

    public ?int $categoryId = null;
    public ?int $brandId = null;

    public function mount(): void
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
        $query = Product::query()
            ->with(['brand', 'categories', 'media', 'prices'])
            ->withCount('orderItems')
            ->withAvg('reviews as average_rating', 'rating')
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        // Apply shared filters
        $query = $this->applySearchFilters($query);
        
        // Apply specific filters
        if ($this->categoryId) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $this->categoryId));
        }
        
        if ($this->brandId) {
            $query->where('brand_id', $this->brandId);
        }

        // Apply sorting
        $query = $this->applySorting($query);

        return $query->paginate(12);
    }

    public function getCategoriesProperty(): Collection
    {
        return Category::where('is_visible', true)->orderBy('name')->get();
    }

    public function getBrandsProperty(): Collection
    {
        return Brand::where('is_visible', true)->orderBy('name')->get();
    }

    public function applyFilters(): void
    {
        $this->resetPage();
        $this->notifySuccess(__('Filters applied successfully'));
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
