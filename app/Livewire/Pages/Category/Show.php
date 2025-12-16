<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Data\Storefront\Home\ProductListItemData;
use App\Livewire\Concerns\WithCart;
use App\Models\Category;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use App\Support\Cache\TagAwareCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property Category $category
 * @property string   $sortBy
 * @property string   $sortDirection
 * @property-read LengthAwarePaginatorContract<int, ProductListItemData> $products
 */
final class Show extends Component
{
    use WithCart;
    use WithPagination;

    public Category $category;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function mount(Category $category): void
    {
        abort_if(! $category->is_visible, 404);

        if (! $category->relationLoaded('media') || ! $category->relationLoaded('translations')) {
            $category->load(['media', 'translations']);
        }

        $this->category = $category;
    }

    /**
     * @return LengthAwarePaginatorContract<int, ProductListItemData>
     */
    #[Computed]
    public function products(): LengthAwarePaginatorContract
    {
        $locale = app()->getLocale();
        $page = request()->integer('page', 1);

        $cacheKey = CacheKeys::categoryShowProducts($this->category->id, $locale, [
            'page'          => $page,
            'sortBy'        => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ]);

        $tags = [
            CacheTags::locale($locale),
            CacheTags::categories(),
            CacheTags::category($this->category->id),
            CacheTags::products(),
            CacheTags::brands(),
        ];

        // Cache each combination of pagination and sorting for a short window to reduce database pressure.
        return TagAwareCache::remember($cacheKey, now()->addSeconds(180), function () use ($locale): LengthAwarePaginatorContract {
            /** @var LengthAwarePaginatorContract<int, Product> $paginator */
            $paginator = $this->category->products()
                ->where('is_visible', true)
                ->forProductList()
                ->withListRelations()
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->orderBy('products.' . $this->sortBy, $this->sortDirection)
                ->paginate(12);

            // Convert Product models to ProductListItemData DTOs
            $items = $paginator->getCollection()->map(fn (Product $product): ProductListItemData => ProductListItemData::fromModel($product, $locale));

            // Create a new paginator with the DTOs
            return new LengthAwarePaginator(
                $items,
                $paginator->total(),
                $paginator->perPage(),
                $paginator->currentPage(),
                [
                    'path'     => request()->url(),
                    'pageName' => 'page',
                ]
            );
        }, $tags);
    }

    public function render(): View
    {
        return view('livewire.pages.category.show', [
            'products' => $this->products,
        ])->layout('components.layouts.base', [
            'title' => $this->category->name,
        ]);
    }
}
