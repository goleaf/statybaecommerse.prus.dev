<?php

declare (strict_types=1);
namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithFilters;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
/**
 * ProductCatalog
 * 
 * Livewire component for ProductCatalog with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 */
final class ProductCatalog extends Component
{
    use WithCart, WithFilters, WithNotifications;
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->resetPage();
    }
    /**
     * Handle products functionality with proper error handling.
     * @return LengthAwarePaginator
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $query = Product::query()->with(['brand', 'categories', 'media', 'prices'])->withCount('orderItems')->withAvg('reviews as average_rating', 'rating')->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now());
        // Apply shared filters from WithFilters trait
        $query = $this->applySearchFilters($query);
        // Apply sorting from WithFilters trait
        $query = $this->applySorting($query);
        return $query->paginate($this->perPage);
    }
    /**
     * Handle categories functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::where('is_visible', true)->orderBy('name')->get()->skipWhile(function ($category) {
            // Skip categories that are not properly configured for display
            return empty($category->name) || !$category->is_visible || empty($category->slug);
        });
    }
    /**
     * Handle brands functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function brands(): Collection
    {
        return Brand::orderBy('name')->get()->skipWhile(function ($brand) {
            // Skip brands that are not properly configured for display
            return empty($brand->name) || !$brand->is_enabled || empty($brand->slug);
        });
    }
    /**
     * Handle applyFilters functionality with proper error handling.
     * @return void
     */
    public function applyFilters(): void
    {
        $this->resetPage();
        $this->notifySuccess(__('Filters applied successfully'));
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.pages.product-catalog', ['products' => $this->products, 'categories' => $this->categories, 'brands' => $this->brands])->layout('components.layouts.base', ['title' => __('Products')]);
    }
}
