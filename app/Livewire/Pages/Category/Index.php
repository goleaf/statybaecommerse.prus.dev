<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Category listing page with reactive filters, caching and schema-driven filters.
 *
 * @property string $search
 * @property array<int, int> $selectedBrandIds
 * @property array<int, int> $selectedCollectionIds
 * @property array<int, int> $selectedCategoryIds
 * @property float|null $priceMin
 * @property float|null $priceMax
 * @property bool $inStock
 * @property bool $onSale
 * @property bool $hasProducts
 * @property string $sort
 * @property bool $sidebarOpen
 * @property-read EloquentCollection<int, Brand> $brands
 * @property-read EloquentCollection<int, Collection> $collections
 */
final class Index extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    #[Url(except: '')]
    public string $search = '';

    /**
     * @var array<int, int>
     */
    #[Url(except: [])]
    public array $selectedBrandIds = [];

    /**
     * @var array<int, int>
     */
    #[Url(except: [])]
    public array $selectedCollectionIds = [];

    /**
     * @var array<int, int>
     */
    #[Url(except: [])]
    public array $selectedCategoryIds = [];

    #[Url(except: null)]
    public ?float $priceMin = null;

    #[Url(except: null)]
    public ?float $priceMax = null;

    #[Url(except: false)]
    public bool $inStock = false;

    #[Url(except: false)]
    public bool $onSale = false;

    #[Url(except: false)]
    public bool $hasProducts = false;

    #[Url(except: 'name_asc')]
    public string $sort = 'name_asc';

    public bool $sidebarOpen = false;

    public function mount(): void
    {
        // Normalize legacy single brand parameter to the modern multi-select input.
        if (property_exists($this, 'brandId') && $this->brandId) {
            $this->selectedBrandIds = [(int) $this->brandId];
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('search')
                ->label(__('Search'))
                ->placeholder(__('Search categories...'))
                ->live(debounce: 400),
            Select::make('brandId')
                ->label(__('Brand'))
                ->placeholder(__('All brands'))
                ->options($this->getBrandOptions())
                ->live(),
            TextInput::make('priceMin')
                ->label(__('Min price'))
                ->numeric()
                ->step(0.01)
                ->minValue(0)
                ->live(debounce: 500),
            TextInput::make('priceMax')
                ->label(__('Max price'))
                ->numeric()
                ->step(0.01)
                ->minValue(0)
                ->live(debounce: 500),
            Checkbox::make('hasProducts')
                ->label(__('Only categories with products'))
                ->live(),
            Select::make('sort')
                ->label(__('Sort by'))
                ->options([
                    'name_asc' => __('Name (A–Z)'),
                    'name_desc' => __('Name (Z–A)'),
                    'products_desc' => __('Most products'),
                    'products_asc' => __('Fewest products'),
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
        return Cache::tags($this->tagsForCategoryIndex([
            CacheTags::brands(),
        ]))->remember(
            CacheKeys::categoryIndexBrands($locale),
            now()->addSeconds(180),
            static function (): EloquentCollection {
                return Brand::query()
                    ->where('is_enabled', true)
                    ->orderBy('name')
                    ->get(['id', 'name']);
            }
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

        return Cache::tags($this->tagsForCategoryIndex([
            CacheTags::collections(),
        ]))->remember(
            CacheKeys::categoryIndexCollections($locale),
            now()->addSeconds(180),
            static function (): EloquentCollection {
                return Collection::query()
                    ->visible()
                    ->orderBy('name')
                    ->get(['id', 'name']);
            }
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

        return Cache::tags($this->tagsForCategoryIndex([
            CacheTags::brands(),
            CacheTags::products(),
        ]))->remember(
            CacheKeys::categoryIndexFacetBrands($locale, $filters),
            now()->addSeconds(180),
            function (): array {
                $brands = Brand::query()
                    ->where('is_enabled', true)
                    ->orderBy('name')
                    ->get(['id', 'name']);

                $countsByBrand = [];
                foreach ($brands as $brand) {
                    $countsByBrand[$brand->id] = $this->baseProductQuery()
                        ->where('brand_id', $brand->id)
                        ->count();
                }

                return $brands
                    ->map(static fn (Brand $brand): array => [
                        'id' => (int) $brand->id,
                        'name' => (string) $brand->name,
                        'count' => (int) ($countsByBrand[$brand->id] ?? 0),
                    ])
                    ->values()
                    ->all();
            }
        );
    }

    /**
     * @return array<int, array{id: int, name: string, count: int}>
     */
    #[Computed]
    public function facetCollections(): array
    {
        $locale = app()->getLocale();
        $filters = $this->filtersForCache();

        return Cache::tags($this->tagsForCategoryIndex([
            CacheTags::collections(),
            CacheTags::products(),
        ]))->remember(
            CacheKeys::categoryIndexFacetCollections($locale, $filters),
            now()->addSeconds(180),
            function (): array {
                $collections = Collection::query()
                    ->visible()
                    ->orderBy('name')
                    ->get(['id', 'name']);

                $countsByCollection = [];
                foreach ($collections as $collection) {
                    $countsByCollection[$collection->id] = $this->baseProductQuery()
                        ->whereHas('collections', fn (Builder $q): Builder => $q->where('collections.id', $collection->id))
                        ->count();
                }

                return $collections
                    ->map(static fn (Collection $collection): array => [
                        'id' => (int) $collection->id,
                        'name' => (string) $collection->name,
                        'count' => (int) ($countsByCollection[$collection->id] ?? 0),
                    ])
                    ->values()
                    ->all();
            }
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

        return Cache::tags($this->tagsForCategoryIndex([
            CacheTags::categories(),
            CacheTags::products(),
        ]))->remember(
            CacheKeys::categoryIndexFacetCategories($locale, $filters),
            now()->addSeconds(180),
            function (): array {
                $categories = Category::query()
                    ->visible()
                    ->orderBy('name')
                    ->get(['id', 'name']);

                $countsByCategory = [];
                foreach ($categories as $category) {
                    $countsByCategory[$category->id] = $this->baseProductQuery()
                        ->whereHas('categories', fn (Builder $q): Builder => $q->where('categories.id', $category->id))
                        ->count();
                }

                return $categories
                    ->map(static fn (Category $category): array => [
                        'id' => (int) $category->id,
                        'name' => (string) $category->name,
                        'count' => (int) ($countsByCategory[$category->id] ?? 0),
                    ])
                    ->values()
                    ->all();
            }
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
            ->when($this->priceMin !== null, fn (Builder $q): Builder => $q->where('price', '>=', (float) $this->priceMin))
            ->when($this->priceMax !== null, fn (Builder $q): Builder => $q->where('price', '<=', (float) $this->priceMax))
            ->when($this->inStock, fn (Builder $q): Builder => $q->where('stock_quantity', '>', 0))
            ->when($this->onSale, fn (Builder $q): Builder => $q->whereNotNull('sale_price'))
            ->when($this->search !== '', function (Builder $q): void {
                $q->where(function (Builder $inner): void {
                    $inner
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%');
                });
            });
    }

    /**
     * @return EloquentCollection<int, Category>
     */
    #[Computed]
    public function categories(): EloquentCollection
    {
        $locale = app()->getLocale();
        $filters = $this->filtersForCache();

        return Cache::tags($this->tagsForCategoryIndex([
            CacheTags::categories(),
            CacheTags::products(),
        ]))->remember(
            CacheKeys::categoryIndexCategories($locale, $filters),
            now()->addSeconds(180),
            function (): EloquentCollection {
                $query = Category::query()
                    ->with(['media'])
                    ->withCount(['products' => function (Builder $q): void {
                        $q->where('is_visible', true)
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
                            ->when($this->priceMin !== null, fn (Builder $qq): Builder => $qq->where('price', '>=', (float) $this->priceMin))
                            ->when($this->priceMax !== null, fn (Builder $qq): Builder => $qq->where('price', '<=', (float) $this->priceMax))
                            ->when($this->inStock, fn (Builder $qq): Builder => $qq->where('stock_quantity', '>', 0))
                            ->when($this->onSale, fn (Builder $qq): Builder => $qq->whereNotNull('sale_price'));
                    }])
                    ->where('is_visible', true);

                if ($this->search !== '') {
                    $query->where(function (Builder $q): void {
                        $q->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('description', 'like', '%'.$this->search.'%');
                    });
                }

                if ($this->hasProducts) {
                    $query->has('products');
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

                return $query->get();
            }
        );
    }

    public function render(): View
    {
        return view('livewire.pages.category.index')
            ->layout('components.layouts.base', [
                'title' => __('Categories'),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersForCache(): array
    {
        return [
            'search' => $this->search,
            'selectedBrandIds' => $this->normalizeIds($this->selectedBrandIds),
            'selectedCollectionIds' => $this->normalizeIds($this->selectedCollectionIds),
            'selectedCategoryIds' => $this->normalizeIds($this->selectedCategoryIds),
            'priceMin' => $this->priceMin,
            'priceMax' => $this->priceMax,
            'inStock' => $this->inStock,
            'onSale' => $this->onSale,
            'hasProducts' => $this->hasProducts,
            'sort' => $this->sort,
        ];
    }

    /**
     * @param  array<int, int|string>  $ids
     * @return array<int, int>
     */
    private function normalizeIds(array $ids): array
    {
        $normalized = array_map(static fn ($value): int => (int) $value, $ids);

        sort($normalized);

        return $normalized;
    }

    /**
     * @param  array<int, string>  $additional
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
