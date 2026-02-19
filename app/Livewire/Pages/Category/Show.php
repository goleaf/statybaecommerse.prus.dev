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
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property Category $category
 * @property string   $sortBy
 * @property string   $sortDirection
 * @property-read LengthAwarePaginatorContract<int, ProductListItemData> $products
 */
final class Show extends Component implements HasForms
{
    use InteractsWithForms;
    use WithCart;
    use WithPagination;

    public Category $category;

    public bool $isIndex = false;

    #[Url]
    public string $search = '';

    /**
     * @var array<int, int|string>
     */
    #[Url]
    public array $categories = [];

    /**
     * @var array<int, int|string>
     */
    #[Url]
    public array $brands = [];

    /**
     * @var array<int|string, array<int, int|string>>
     */
    #[Url]
    public array $selectedAttributes = [];

    #[Url]
    public ?float $minPrice = null;

    #[Url]
    public ?float $maxPrice = null;

    #[Url]
    public bool $inStock = false;

    #[Url]
    public bool $onSale = false;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    /**
     * @var array<int, string>
     */
    private const SORTABLE_COLUMNS = ['created_at', 'name', 'price', 'rating'];

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

        $this->sortBy = $this->normalizeSortBy($this->sortBy);
        $this->sortDirection = $this->normalizeSortDirection($this->sortDirection);

        $this->search = $this->sanitizeSearch($this->search);
        $this->categories = $this->normalizeIdList($this->categories);
        $this->brands = $this->normalizeIdList($this->brands);
        $this->selectedAttributes = $this->normalizeSelectedAttributes($this->selectedAttributes);
        $this->minPrice = $this->normalizeNullableFloat($this->minPrice);
        $this->maxPrice = $this->normalizeNullableFloat($this->maxPrice);
        $this->inStock = $this->normalizeBool($this->inStock);
        $this->onSale = $this->normalizeBool($this->onSale);
    }

    #[On('filter-updated')]
    public function applyFilters(mixed $filters = null): void
    {
        if (! is_array($filters)) {
            $filters = [];
        }

        if (array_key_exists('search', $filters)) {
            $this->search = $this->sanitizeSearch($filters['search']);
        }

        if (array_key_exists('categories', $filters)) {
            $this->categories = is_array($filters['categories'])
                ? $this->normalizeIdList($filters['categories'])
                : [];
        }

        if (array_key_exists('brands', $filters)) {
            $this->brands = is_array($filters['brands'])
                ? $this->normalizeIdList($filters['brands'])
                : [];
        }

        if (array_key_exists('selectedAttributes', $filters)) {
            $this->selectedAttributes = is_array($filters['selectedAttributes'])
                ? $this->normalizeSelectedAttributes($filters['selectedAttributes'])
                : [];
        }

        if (array_key_exists('minPrice', $filters)) {
            $this->minPrice = $this->normalizeNullableFloat($filters['minPrice']);
        }

        if (array_key_exists('maxPrice', $filters)) {
            $this->maxPrice = $this->normalizeNullableFloat($filters['maxPrice']);
        }

        if (array_key_exists('inStock', $filters)) {
            $this->inStock = $this->normalizeBool($filters['inStock']);
        }

        if (array_key_exists('onSale', $filters)) {
            $this->onSale = $this->normalizeBool($filters['onSale']);
        }

        if (array_key_exists('sortBy', $filters)) {
            $this->sortBy = $this->normalizeSortBy((string) $filters['sortBy']);
        }

        if (array_key_exists('sortDirection', $filters)) {
            $this->sortDirection = $this->normalizeSortDirection((string) $filters['sortDirection']);
        }

        $this->resetPage();
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
            'id'       => $cat->id,
            'name'     => $cat->name,
            'slug'     => $cat->slug,
            'children' => $cat->children->map(fn ($child) => [
                'id'       => $child->id,
                'name'     => $child->name,
                'slug'     => $child->slug,
                'children' => $child->children->map(fn ($gc) => [
                    'id'   => $gc->id,
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
        $sortBy = $this->normalizeSortBy($this->sortBy);
        $sortDirection = $this->normalizeSortDirection($this->sortDirection);

        $cacheKey = CacheKeys::categoryShowProducts($this->category->id, $locale, [
            'page'          => $page,
            'sortBy'        => $sortBy,
            'sortDirection' => $sortDirection,
            ...$this->filtersForCache(),
        ]);

        $tags = array_values(array_unique(array_merge([
            CacheTags::locale($locale),
            CacheTags::categories(),
            CacheTags::category($this->category->id),
            CacheTags::products(),
            CacheTags::brands(),
            CacheTags::reviews(),
        ], CacheTags::brandIds($this->brands), CacheTags::categoryIds($this->categories))));

        // Cache each combination of pagination and sorting for a short window to reduce database pressure.
        return TagAwareCache::remember($cacheKey, now()->addSeconds(180), function () use ($locale, $sortBy, $sortDirection): LengthAwarePaginatorContract {
            /** @var LengthAwarePaginatorContract<int, Product> $paginator */
            $query = $this->category->products()
                ->published()
                ->forProductList()
                ->withListRelations();

            $filteredQuery = $this->applyFiltersToQuery($query, $locale);

            $paginator = $this->applySorting($filteredQuery, $sortBy, $sortDirection)
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

    public function updatedSortBy(string $value): void
    {
        $this->sortBy = $this->normalizeSortBy($value);
        $this->resetPage();
    }

    public function updatedSortDirection(string $value): void
    {
        $this->sortDirection = $this->normalizeSortDirection($value);
        $this->resetPage();
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersForCache(): array
    {
        return [
            'search'             => $this->search,
            'categories'         => $this->categories,
            'brands'             => $this->brands,
            'selectedAttributes' => $this->selectedAttributes,
            'minPrice'           => $this->minPrice,
            'maxPrice'           => $this->maxPrice,
            'inStock'            => $this->inStock,
            'onSale'             => $this->onSale,
        ];
    }

    private function normalizeSortBy(string $value): string
    {
        return in_array($value, self::SORTABLE_COLUMNS, true) ? $value : 'created_at';
    }

    private function normalizeSortDirection(string $value): string
    {
        return strtolower($value) === 'asc' ? 'asc' : 'desc';
    }

    /**
     * @param  BelongsToMany<Product, Category>  $query
     * @return BelongsToMany<Product, Category>
     */
    private function applyFiltersToQuery(BelongsToMany $query, string $locale): BelongsToMany
    {
        if ($this->search !== '') {
            $term = $this->search;

            $query->where(function ($subQuery) use ($term, $locale): void {
                $subQuery
                    ->where('products.name', 'like', '%' . $term . '%')
                    ->orWhere('products.description', 'like', '%' . $term . '%')
                    ->orWhere('products.short_description', 'like', '%' . $term . '%')
                    ->orWhere('products.sku', 'like', '%' . $term . '%')
                    ->orWhereHas('translations', function ($translationQuery) use ($term, $locale): void {
                        $translationQuery
                            ->where('locale', $locale)
                            ->where(function ($translationTerms) use ($term): void {
                                $translationTerms
                                    ->where('name', 'like', '%' . $term . '%')
                                    ->orWhere('description', 'like', '%' . $term . '%')
                                    ->orWhere('short_description', 'like', '%' . $term . '%');
                            });
                    });
            });
        }

        if ($this->categories !== []) {
            $query->whereHas('categories', function ($categoryQuery): void {
                $categoryQuery->whereIn('categories.id', $this->categories);
            });
        }

        if ($this->brands !== []) {
            $query->whereIn('products.brand_id', $this->brands);
        }

        if ($this->minPrice !== null) {
            $query->where('products.price', '>=', $this->minPrice);
        }

        if ($this->maxPrice !== null) {
            $query->where('products.price', '<=', $this->maxPrice);
        }

        if ($this->inStock) {
            $query->where('products.stock_quantity', '>', 0);
        }

        if ($this->onSale && Schema::hasTable('discount_products') && Schema::hasTable('discounts')) {
            $query->whereHas('discounts', static function ($discountQuery): void {
                $discountQuery->active();
            });
        }

        foreach ($this->selectedAttributes as $attributeId => $valueIds) {
            if ($valueIds === []) {
                continue;
            }

            $query->whereHas('attributes', function ($attributeQuery) use ($attributeId, $valueIds): void {
                $attributeQuery
                    ->where('attributes.id', $attributeId)
                    ->wherePivotIn('attribute_value_id', $valueIds);
            });
        }

        return $query;
    }

    private function sanitizeSearch(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return trim(mb_substr($value, 0, 120));
    }

    /**
     * @param  array<int, int|string>  $values
     * @return array<int, int>
     */
    private function normalizeIdList(array $values): array
    {
        $normalized = array_map(static fn ($value): int => (int) $value, $values);
        $normalized = array_values(array_filter($normalized, static fn (int $value): bool => $value > 0));
        $normalized = array_values(array_unique($normalized));

        sort($normalized);

        return $normalized;
    }

    /**
     * @param  array<int|string, array<int, int|string>>  $selectedAttributes
     * @return array<int, array<int, int>>
     */
    private function normalizeSelectedAttributes(array $selectedAttributes): array
    {
        $normalized = [];

        foreach ($selectedAttributes as $attributeId => $valueIds) {
            if (! is_array($valueIds)) {
                continue;
            }

            $normalizedAttributeId = (int) $attributeId;

            if ($normalizedAttributeId <= 0) {
                continue;
            }

            $normalizedValueIds = $this->normalizeIdList($valueIds);

            if ($normalizedValueIds === []) {
                continue;
            }

            $normalized[$normalizedAttributeId] = $normalizedValueIds;
        }

        ksort($normalized);

        return $normalized;
    }

    private function normalizeNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $normalized = (float) $value;

        return $normalized >= 0 ? $normalized : null;
    }

    private function normalizeBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    /**
     * @param  BelongsToMany<Product, Category>  $query
     * @return BelongsToMany<Product, Category>
     */
    private function applySorting(BelongsToMany $query, string $sortBy, string $sortDirection): BelongsToMany
    {
        return match ($sortBy) {
            'name'   => $query->orderBy('products.name', $sortDirection),
            'price'  => $query->orderBy('products.price', $sortDirection),
            'rating' => $this->applyRatingSorting($query, $sortDirection),
            default  => $query->orderBy('products.created_at', $sortDirection),
        };
    }

    /**
     * @param  BelongsToMany<Product, Category>  $query
     * @return BelongsToMany<Product, Category>
     */
    private function applyRatingSorting(BelongsToMany $query, string $sortDirection): BelongsToMany
    {
        if (
            ! Schema::hasTable('reviews')
            || ! Schema::hasColumn('reviews', 'product_id')
            || ! Schema::hasColumn('reviews', 'rating')
        ) {
            return $query->orderBy('products.created_at', 'desc');
        }

        $approvalCondition = Schema::hasColumn('reviews', 'is_approved')
            ? ' and reviews.is_approved = 1'
            : '';

        $query->orderByRaw(
            '(select coalesce(avg(reviews.rating), 0) from reviews where reviews.product_id = products.id'
            . $approvalCondition
            . ') '
            . $sortDirection
        );

        return $query->orderBy('products.created_at', 'desc');
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
