<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Models\Category;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
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
/**
 * @property-read LengthAwarePaginatorContract<int, Product> $products
 */
final class Show extends Component
{
    use WithPagination;

    public Category $category;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(Category $category): void
    {
        // Ensure category is visible and load media and translations
        if (! $category->is_visible) {
            abort(404);
        }
        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        if (! $category->relationLoaded('media') || ! $category->relationLoaded('translations')) {
            $category->load(['media', 'translations']);
        }
        $this->category = $category;
    }

    /**
     * Handle products functionality with proper error handling and performance optimization.
     */
    /**
     * @return LengthAwarePaginatorContract<int, Product>
     */
    #[Computed]
    public function products(): LengthAwarePaginatorContract
    {
        $locale = app()->getLocale();
        $page = request()->integer('page', 1);

        $cacheKey = CacheKeys::categoryShowProducts($this->category->id, $locale, [
            'page' => $page,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ]);

        $tags = [
            CacheTags::locale($locale),
            CacheTags::categories(),
            CacheTags::category($this->category->id),
            CacheTags::products(),
            CacheTags::brands(),
        ];

        return Cache::tags($tags)->remember(
            $cacheKey,
            now()->addSeconds(180),
            function () {
                /** @var \Illuminate\Pagination\LengthAwarePaginator<int, Product> $paginator */
                $paginator = $this->category->products()
                    ->where('is_visible', true)
                    ->with([
                        'brand:id,name,slug',
                        'media' => function ($query): void {
                            $query->select('id', 'model_id', 'model_type', 'name', 'file_name', 'disk', 'conversions_disk', 'size', 'mime_type', 'manipulations', 'custom_properties', 'generated_conversions', 'responsive_images', 'order_column', 'created_at', 'updated_at')
                                ->where('collection_name', 'images')
                                ->orderBy('order_column');
                        },
                    ])
                    ->select([
                        'products.id', 'products.name', 'products.slug', 'products.description', 'products.short_description', 'products.sku', 'products.price', 'products.sale_price',
                        'products.compare_price', 'products.cost_price', 'products.manage_stock', 'products.stock_quantity', 'products.low_stock_threshold',
                        'products.weight', 'products.length', 'products.width', 'products.height', 'products.is_visible', 'products.is_enabled', 'products.is_featured',
                        'products.published_at', 'products.seo_title', 'products.seo_description', 'products.brand_id', 'products.status', 'products.type',
                        'products.created_at', 'products.updated_at', 'products.deleted_at',
                    ])
                    ->withCount('reviews')
                    ->withAvg('reviews', 'rating')
                    ->orderBy('products.'.$this->sortBy, $this->sortDirection)
                    ->paginate(12);

                return $paginator;
            }
        );
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.pages.category.show', ['products' => $this->products])->layout('components.layouts.base', ['title' => $this->category->name]);
    }
}
