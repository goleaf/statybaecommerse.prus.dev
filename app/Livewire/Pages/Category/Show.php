<?php declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Show
 *
 * Livewire component for Show with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property Category $category
 * @property string $sortBy
 * @property string $sortDirection
 */
final class Show extends Component
{
    use WithPagination;

    public Category $category;
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    /**
     * Initialize the Livewire component with parameters.
     * @param Category $category
     * @return void
     */
    public function mount(Category $category): void
    {
        // Ensure category is visible and load media and translations
        if (!$category->is_visible) {
            abort(404);
        }
        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        if (!$category->relationLoaded('media') || !$category->relationLoaded('translations')) {
            $category->load(['media', 'translations']);
        }
        $this->category = $category;
    }

    /**
     * Handle products functionality with proper error handling and performance optimization.
     * @return LengthAwarePaginator
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        return $this
            ->category
            ->products()
            ->where('is_visible', true)
            ->with([
                'brand:id,name,slug',
                'media' => function ($query) {
                    $query
                        ->select('id', 'model_id', 'model_type', 'name', 'file_name', 'disk', 'conversions_disk', 'size', 'mime_type', 'manipulations', 'custom_properties', 'generated_conversions', 'responsive_images', 'order_column', 'created_at', 'updated_at')
                        ->where('collection_name', 'images')
                        ->orderBy('order_column');
                }
            ])
            ->select([
                'id', 'name', 'slug', 'description', 'short_description', 'sku', 'price', 'sale_price',
                'compare_price', 'cost_price', 'manage_stock', 'stock_quantity', 'low_stock_threshold',
                'weight', 'length', 'width', 'height', 'is_visible', 'is_enabled', 'is_featured',
                'published_at', 'seo_title', 'seo_description', 'brand_id', 'status', 'type',
                'created_at', 'updated_at', 'deleted_at'
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(12);
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.pages.category.show', ['products' => $this->products])->layout('components.layouts.base', ['title' => $this->category->name]);
    }
}
