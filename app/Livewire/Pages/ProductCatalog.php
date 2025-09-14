<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithFilters;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

final /**
 * ProductCatalog
 * 
 * Livewire component for reactive frontend functionality.
 */
class ProductCatalog extends Component
{
    use WithCart, WithFilters, WithNotifications;

    public function mount(): void
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

        // Apply shared filters from WithFilters trait
        $query = $this->applySearchFilters($query);

        // Apply sorting from WithFilters trait
        $query = $this->applySorting($query);

        return $query->paginate($this->perPage);
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
            'title' => __('Products'),
        ]);
    }
}
