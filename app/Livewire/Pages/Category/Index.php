<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Services\FacetCountingService;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use App\Support\Cache\TagAwareCache;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Support\Collection as SupportCollection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Category listing page with reactive filters, caching and schema-driven filters.
 *
 * @property string          $search
 * @property string          $brandSearch
 * @property array<int, int> $selectedBrandIds
 * @property array<int, int> $selectedCollectionIds
 * @property array<int, int> $selectedCategoryIds
 * @property bool            $inStock
 * @property bool            $onSale
 * @property bool            $hasProducts
 * @property string          $sort
 * @property bool            $sidebarOpen
 * @property-read EloquentCollection<int, Brand> $brands
 * @property-read EloquentCollection<int, Collection> $collections
 */
final class Index extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public string $search = '';

    public string $brandSearch = '';

    /**
     * @var array<int, int>
     */
    public array $selectedBrandIds = [];

    /**
     * @var array<int, int>
     */
    public array $selectedCollectionIds = [];

    /**
     * @var array<int, int>
     */
    public array $selectedCategoryIds = [];

    public bool $inStock = false;

    public bool $onSale = false;

    public bool $hasProducts = false;

    public string $sort = 'name_asc';

    public bool $sidebarOpen = false;

    public bool $isIndex = true;

    #[Computed]
    public function pageTitle(): string
    {
        return __('categories.index.meta_title');
    }

    #[Computed]
    public function pageDescription(): string
    {
        return __('categories.index.meta_description');
    }

    public function mount(): void
    {
        // Normalize legacy single brand parameter to the modern multi-select input.
        if (property_exists($this, 'brandId') && $this->brandId) {
            $this->selectedBrandIds = [(int) $this->brandId];
        }
    }

    public function openSidebar(): void
    {
        $this->sidebarOpen = true;
    }

    public function closeSidebar(): void
    {
        $this->sidebarOpen = false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('search')
                ->label(__('messages.search'))
                ->placeholder(__('messages.search_categories'))
                ->live(debounce: 400),
            Select::make('brandId')
                ->label(__('messages.brand'))
                ->placeholder(__('messages.all_brands'))
                ->options($this->getBrandOptions())
                ->live(),
            Checkbox::make('hasProducts')
                ->label(__('messages.only_categories_with_products'))
                ->live(),
            Select::make('sort')
                ->label(__('messages.sort_by'))
                ->options([
                    'name_asc'      => __('messages.name_a_z'),
                    'name_desc'     => __('messages.name_z_a'),
                    'products_desc' => __('messages.most_products'),
                    'products_asc'  => __('messages.fewest_products'),
                ])
                ->live(),
        ]);
    }

    /**
     * @return EloquentCollection<int, Brand>
     */
    #[Computed]
    public function brands(): EloquentCollection
    {
        $locale = app()->getLocale();

        // Tag caches so we can flush locale-specific data when catalogue data changes.
        return TagAwareCache::remember(
            CacheKeys::categoryIndexBrands($locale),
            now()->addSeconds(180),
            static function (): EloquentCollection {
                return Brand::query()
                    ->where('is_enabled', true)
                    ->orderBy('name')
                    ->get(['id', 'name']);
            },
            $this->tagsForCategoryIndex([
                CacheTags::brands(),
            ])
        );
    }

    /**
     * @return array<int, string>
     */
    private function getBrandOptions(): array
    {
        /** @var \Illuminate\Support\Collection<int, string|null> $brandNames */
        $brandNames = $this->brands->pluck('name', 'id');

        return $brandNames
            ->filter(static fn (?string $label): bool => filled($label))
            ->mapWithKeys(static fn (?string $label, int $id): array => [$id => (string) $label])
            ->all();
    }

    /**
     * @return EloquentCollection<int, Collection>
     */
    #[Computed]
    public function collections(): EloquentCollection
    {
        $locale = app()->getLocale();

        return TagAwareCache::remember(
            CacheKeys::categoryIndexCollections($locale),
            now()->addSeconds(180),
            static function (): EloquentCollection {
                return Collection::query()
                    ->visible()
                    ->orderBy('name')
                    ->get(['id', 'name']);
            },
            $this->tagsForCategoryIndex([
                CacheTags::collections(),
            ])
        );
    }

    /**
     * @return array<int, array{id: int, name: string, count: int}>
     */
    #[Computed]
    public function facetBrands(): array
    {
        $locale = app()->getLocale();
        $filters = $this->filtersForCache();

        return TagAwareCache::remember(
            CacheKeys::categoryIndexFacetBrands($locale, $filters),
            now()->addSeconds(180),
            function (): array {
                $facetCountingService = app(FacetCountingService::class);
                $facetCountingService->resetQueryCount();

                return $facetCountingService->getBrandFacets($this->baseProductQuery());
            },
            $this->tagsForCategoryIndex([
                CacheTags::brands(),
                CacheTags::products(),
            ])
        );
    }

    /**
     * @return array<int, array{id: int, name: string, count: int}>
     */
    #[Computed]
    public function filteredFacetBrands(): array
    {
        $needle = mb_strtolower(trim($this->brandSearch));

        if ($needle === '') {
            return $this->facetBrands;
        }

        return array_values(array_filter(
            $this->facetBrands,
            static fn (array $brand): bool => str_contains(
                mb_strtolower((string) ($brand['name'] ?? '')),
                $needle
            )
        ));
    }

    /**
     * @return array<int, array{id: int, name: string, count: int}>
     */
    #[Computed]
    public function facetCollections(): array
    {
        $locale = app()->getLocale();
        $filters = $this->filtersForCache();

        return TagAwareCache::remember(
            CacheKeys::categoryIndexFacetCollections($locale, $filters),
            now()->addSeconds(180),
            function (): array {
                $facetCountingService = app(FacetCountingService::class);
                $facetCountingService->resetQueryCount();

                return $facetCountingService->getCollectionFacets($this->baseProductQuery());
            },
            $this->tagsForCategoryIndex([
                CacheTags::collections(),
                CacheTags::products(),
            ])
        );
    }

    /**
     * @return array<int, array{id: int, name: string, count: int}>
     */
    #[Computed]
    public function facetCategories(): array
    {
        $locale = app()->getLocale();
        $filters = $this->filtersForCache();

        return TagAwareCache::remember(
            CacheKeys::categoryIndexFacetCategories($locale, $filters),
            now()->addSeconds(180),
            function (): array {
                $facetCountingService = app(FacetCountingService::class);
                $facetCountingService->resetQueryCount();

                return $facetCountingService->getCategoryFacets($this->baseProductQuery());
            },
            $this->tagsForCategoryIndex([
                CacheTags::categories(),
                CacheTags::products(),
            ])
        );
    }

    /**
     * @return Builder<Product>
     */
    private function baseProductQuery(): Builder
    {
        return Product::query()
            ->visible()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when(! empty($this->selectedBrandIds), fn (Builder $q): Builder => $q->whereIn('brand_id', $this->selectedBrandIds))
            ->when(
                ! empty($this->selectedCollectionIds),
                fn (Builder $q): Builder => $q->whereHas(
                    'collections',
                    fn (Builder $inner): Builder => $inner->whereIn('collections.id', $this->selectedCollectionIds)
                )
            )
            ->when($this->inStock, fn (Builder $q): Builder => $this->applyInStockProductFilter($q))
            ->when(
                $this->onSale && SchemaFacade::hasTable('discount_products') && SchemaFacade::hasTable('discounts'),
                fn (Builder $q): Builder => $q->whereHas('discounts', static fn (Builder $discountQuery): Builder => $discountQuery->active())
            )
            ->when($this->search !== '', function (Builder $q): void {
                $q->where(function (Builder $inner): void {
                    $inner
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%');
                });
            });
    }
    /**
     * @return SupportCollection<int, array{category: Category, depth: int}>
     */
    #[Computed]
    public function categories(): SupportCollection
    {
        $query = Category::query()
            ->with(['media'])
            ->withCount(['products' => function (Builder $q): void {
                $this->applyActiveProductFilters($q);
            }])
            ->where('is_visible', true);

        if ($this->search !== '') {
            $query->where(function (Builder $q): void {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->hasProducts) {
            $query->has('products');
        }

        if ($this->inStock) {
            $query->whereHas('products', function (Builder $q): void {
                $this->applyActiveProductFilters($q);
            });
        }

        if (! empty($this->selectedBrandIds)) {
            $query->whereHas('products', function (Builder $q): void {
                $this->applyActiveProductFilters($q);
            });
        }

        if (! empty($this->selectedCategoryIds)) {
            $query->where(function (Builder $q): void {
                $q->whereIn('id', $this->selectedCategoryIds)
                    ->orWhereIn('parent_id', $this->selectedCategoryIds);
            });
        }

        $query
            ->when($this->sort === 'name_asc', fn (Builder $q): Builder => $q->orderBy('name'))
            ->when($this->sort === 'name_desc', fn (Builder $q): Builder => $q->orderByDesc('name'))
            ->when($this->sort === 'products_desc', fn (Builder $q): Builder => $q->orderByDesc('products_count'))
            ->when($this->sort === 'products_asc', fn (Builder $q): Builder => $q->orderBy('products_count'))
            ->when(
                ! in_array($this->sort, ['name_asc', 'name_desc', 'products_desc', 'products_asc'], true),
                fn (Builder $q): Builder => $q->orderBy('name')
            );

        /** @var EloquentCollection<int, Category> $categories */
        $categories = $query->get();

        /** @var array<int, bool> $visibleIds */
        $visibleIds = array_fill_keys(
            $categories->pluck('id')->map(static fn ($id): int => (int) $id)->all(),
            true
        );

        /** @var array<int, EloquentCollection<int, Category>> $categoriesByParent */
        $categoriesByParent = [];

        foreach ($categories as $category) {
            $parentId = (int) ($category->parent_id ?? 0);

            // When a filtered result contains children without their parents, render them at root level.
            if ($parentId !== 0 && ! isset($visibleIds[$parentId])) {
                $parentId = 0;
            }

            if (! isset($categoriesByParent[$parentId])) {
                $categoriesByParent[$parentId] = new EloquentCollection;
            }

            $categoriesByParent[$parentId]->push($category);
        }

        return $this->flattenCategoryRows($categoriesByParent, 0, 0);
    }

    /**
     * @param  array<int, EloquentCollection<int, Category>>  $categoriesByParent
     * @return SupportCollection<int, array{category: Category, depth: int}>
     */
    private function flattenCategoryRows(array $categoriesByParent, int $parentId, int $depth): SupportCollection
    {
        $rows = new SupportCollection;
        $siblings = $categoriesByParent[$parentId] ?? new EloquentCollection;

        foreach ($this->sortCategorySiblings($siblings) as $category) {
            $rows->push([
                'category' => $category,
                'depth'    => $depth,
            ]);

            $rows = $rows->merge(
                $this->flattenCategoryRows($categoriesByParent, (int) $category->getKey(), $depth + 1)
            );
        }

        return $rows;
    }

    /**
     * @param  EloquentCollection<int, Category>  $siblings
     * @return EloquentCollection<int, Category>
     */
    private function sortCategorySiblings(EloquentCollection $siblings): EloquentCollection
    {
        return $siblings
            ->sort(function (Category $left, Category $right): int {
                $leftName = mb_strtolower((string) $left->name);
                $rightName = mb_strtolower((string) $right->name);

                return match ($this->sort) {
                    'name_desc' => strcmp($rightName, $leftName),
                    'products_desc' => ((int) ($right->products_count ?? 0) <=> (int) ($left->products_count ?? 0))
                        ?: strcmp($leftName, $rightName),
                    'products_asc' => ((int) ($left->products_count ?? 0) <=> (int) ($right->products_count ?? 0))
                        ?: strcmp($leftName, $rightName),
                    default => strcmp($leftName, $rightName),
                };
            })
            ->values();
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    private function applyActiveProductFilters(Builder $query): Builder
    {
        return $query
            ->published()
            ->when(
                ! empty($this->selectedBrandIds),
                fn (Builder $qq): Builder => $qq->whereIn('brand_id', $this->selectedBrandIds)
            )
            ->when(
                ! empty($this->selectedCollectionIds),
                fn (Builder $qq): Builder => $qq->whereHas(
                    'collections',
                    fn (Builder $c): Builder => $c->whereIn('collections.id', $this->selectedCollectionIds)
                )
            )
            ->when($this->inStock, fn (Builder $qq): Builder => $this->applyInStockProductFilter($qq))
            ->when(
                $this->onSale && SchemaFacade::hasTable('discount_products') && SchemaFacade::hasTable('discounts'),
                fn (Builder $qq): Builder => $qq->whereHas('discounts', static fn (Builder $discountQuery): Builder => $discountQuery->active())
            );
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    private function applyInStockProductFilter(Builder $query): Builder
    {
        $supportsVariants = SchemaFacade::hasTable('product_variants')
            && SchemaFacade::hasTable('product_variant_product');
        $variantHasTrackInventory = $supportsVariants && SchemaFacade::hasColumn('product_variants', 'track_inventory');
        $variantHasStockQuantity = $supportsVariants && SchemaFacade::hasColumn('product_variants', 'stock_quantity');

        return $query->where(function (Builder $stockQuery) use (
            $supportsVariants,
            $variantHasTrackInventory,
            $variantHasStockQuantity
        ): void {
            $stockQuery->where('stock_quantity', '>', 0);

            if (! $supportsVariants || (! $variantHasTrackInventory && ! $variantHasStockQuantity)) {
                return;
            }

            $stockQuery->orWhereHas('variants', function (Builder $variantQuery) use (
                $variantHasTrackInventory,
                $variantHasStockQuantity
            ): void {
                $variantQuery->where(function (Builder $variantStockQuery) use (
                    $variantHasTrackInventory,
                    $variantHasStockQuantity
                ): void {
                    if ($variantHasTrackInventory) {
                        $variantStockQuery->where('track_inventory', false);
                    }

                    if ($variantHasStockQuantity) {
                        if ($variantHasTrackInventory) {
                            $variantStockQuery->orWhere('stock_quantity', '>', 0);
                        } else {
                            $variantStockQuery->where('stock_quantity', '>', 0);
                        }
                    }
                });
            });
        });
    }

    public function render(): View
    {
        $appName = config('app.name');
        $title = $this->pageTitle();

        if (is_string($appName) && $appName !== '') {
            $title .= ' - ' . $appName;
        }

        return view('livewire.pages.category.index')
            ->layout('components.layouts.base', [
                'title'       => $title,
                'description' => $this->pageDescription(),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersForCache(): array
    {
        return [
            'search'                => $this->search,
            'selectedBrandIds'      => $this->normalizeIds($this->selectedBrandIds),
            'selectedCollectionIds' => $this->normalizeIds($this->selectedCollectionIds),
            'selectedCategoryIds'   => $this->normalizeIds($this->selectedCategoryIds),
            'inStock'               => $this->inStock,
            'onSale'                => $this->onSale,
            'hasProducts'           => $this->hasProducts,
            'sort'                  => $this->sort,
        ];
    }

    /**
     * @param  array<int, int|string> $ids
     * @return array<int, int>
     */
    private function normalizeIds(array $ids): array
    {
        $normalized = array_map(static fn ($value): int => (int) $value, $ids);

        sort($normalized);

        return $normalized;
    }

    /**
     * @param  array<int, string> $additional
     * @return array<int, string>
     */
    private function tagsForCategoryIndex(array $additional = []): array
    {
        $base = [
            CacheTags::locale(app()->getLocale()),
            CacheTags::categories(),
            CacheTags::products(),
            CacheTags::brands(),
            CacheTags::collections(),
        ];

        $dynamic = array_merge(
            CacheTags::brandIds($this->selectedBrandIds),
            CacheTags::collectionIds($this->selectedCollectionIds),
            CacheTags::categoryIds($this->selectedCategoryIds),
        );

        return array_values(array_unique(array_merge($base, $dynamic, $additional)));
    }
}
