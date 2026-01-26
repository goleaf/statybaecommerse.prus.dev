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
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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
final class Show extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use WithCart;
    use WithPagination;

    public Category $category;

    public bool $isIndex = false;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function mount(?Category $category = null): void
    {
        if ($category && $category->exists) {
            abort_if(! $category->is_visible, 404);

            if (! $category->relationLoaded('media') || ! $category->relationLoaded('translations')) {
                $category->load(['media', 'translations']);
            }

            $this->category = $category;
            $this->isIndex = false;
        } else {
            $this->isIndex = true;
        }
    }

    #[Computed]
    public function pageTitle(): string
    {
        return $this->isIndex ? __('messages.categories_index_meta_title') : $this->category->name;
    }

    #[Computed]
    public function pageDescription(): string
    {
        return $this->isIndex ? __('messages.categories_index_meta_description') : ($this->category->description ?? '');
    }

    #[Computed]
    public function categoryTree(): \Illuminate\Support\Collection
    {
        $roots = Category::query()
            ->where('is_visible', true)
            ->whereNull('parent_id')
            ->with([
                'children' => function ($q) {
                    $q->where('is_visible', true)
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->with([
                            'children' => function ($qq) {
                                $qq->where('is_visible', true)->orderBy('sort_order')->orderBy('name');
                            },
                        ]);
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $roots->map(fn ($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'slug' => $cat->slug,
            'children' => $cat->children->map(fn ($child) => [
                'id' => $child->id,
                'name' => $child->name,
                'slug' => $child->slug,
                'children' => $child->children->map(fn ($gc) => [
                    'id' => $gc->id,
                    'name' => $gc->name,
                    'slug' => $gc->slug,
                ])->values(),
            ])->values(),
        ])->values();
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
