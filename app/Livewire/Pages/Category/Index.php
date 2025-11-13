<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Category;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
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
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Category listing page with reactive filters, caching and schema-driven filters.
 *
 * @property string          $search
 * @property array<int, int> $selectedBrandIds
 * @property array<int, int> $selectedCollectionIds
 * @property array<int, int> $selectedCategoryIds
 * @property float|null      $priceMin
 * @property float|null      $priceMax
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

    public function boot(): void
    {
        // Ensure locale is set from route parameter on every request (including AJAX)
        // This must happen before any translations are used
        $this->ensureLocale();
    }

    public function mount(): void
    {
        // Ensure locale is set from route parameter
        $this->ensureLocale();

        // Normalize legacy single brand parameter to the modern multi-select input.
        if (property_exists($this, 'brandId') && $this->brandId) {
            $this->selectedBrandIds = [(int) $this->brandId];
        }
    }

    /**
     * Ensure locale is set from route parameter.
     * This mirrors the SetLocale middleware logic to ensure locale is set correctly
     * for both initial page load and Livewire AJAX requests.
     */
    private function ensureLocale(): void
    {
        $request = request();
        
        // Get supported locales (config can be string or array)
        $supportedConfig = config('app.supported_locales', 'lt,en');
        $supportedLocales = [];
        if (is_array($supportedConfig)) {
            $supportedLocales = array_filter($supportedConfig, static fn ($locale): bool => is_string($locale) && $locale !== '');
        } elseif (is_string($supportedConfig)) {
            $supportedLocales = array_filter(
                array_map(
                    static fn (string $locale): string => trim($locale),
                    explode(',', $supportedConfig)
                ),
                static fn (string $locale): bool => $locale !== ''
            );
        }
        $supportedLocales = array_values(array_map(
            static fn (string $locale): string => trim($locale),
            $supportedLocales
        ));
        
        // Prefer locale from route parameter if present (e.g., /{locale}/...)
        $routeLocale = $request->route('locale');
        // Allow explicit override via query (?locale=xx)
        $queryLocale = $request->query('locale');
        
        // Get locale from query, header, session, cookie, or user preference
        $defaultLocaleConfig = config('app.locale', 'lt');
        $defaultLocale = is_string($defaultLocaleConfig) && $defaultLocaleConfig !== ''
            ? $defaultLocaleConfig
            : 'lt';
        
        $candidateLocales = array_values(array_filter([
            $routeLocale,
            $queryLocale,
            session('locale'),
            session('app.locale'),
            $request->cookie('app_locale'),
            auth()->check() ? (auth()->user()->preferred_locale ?? null) : null,
        ], static fn ($candidate): bool => is_string($candidate) && $candidate !== ''));
        
        $locale = $defaultLocale;
        
        foreach ($candidateLocales as $candidate) {
            if (in_array($candidate, $supportedLocales, true)) {
                $locale = $candidate;
                break;
            }
        }
        
        if (!in_array($locale, $supportedLocales, true)) {
            $fallbackLocaleConfig = config('app.fallback_locale');
            $fallbackLocale = is_string($fallbackLocaleConfig) && $fallbackLocaleConfig !== ''
                ? $fallbackLocaleConfig
                : $defaultLocale;
            
            if (in_array($fallbackLocale, $supportedLocales, true)) {
                $locale = $fallbackLocale;
            } elseif (in_array($defaultLocale, $supportedLocales, true)) {
                $locale = $defaultLocale;
            } elseif ($supportedLocales !== []) {
                $locale = $supportedLocales[0];
            } else {
                $locale = $defaultLocale;
            }
        }
        
        // Set application locale (this is critical for translations to work)
        app()->setLocale($locale);
        app()->instance('request_locale', $locale);
        
        // Store in session and cookie for persistence (mirror middleware behavior)
        session()->put('locale', $locale);
        session()->put('app.locale', $locale);
        cookie()->queue(cookie('app_locale', $locale, 60 * 24 * 30));
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
                    'name_asc'      => __('Name (A–Z)'),
                    'name_desc'     => __('Name (Z–A)'),
                    'products_desc' => __('Most products'),
                    'products_asc'  => __('Fewest products'),
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
                        'id'    => (int) $brand->id,
                        'name'  => (string) $brand->name,
                        'count' => (int) ($countsByBrand[$brand->id] ?? 0),
                    ])
                    ->values()
                    ->all();
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
    public function facetCollections(): array
    {
        $locale = app()->getLocale();
        $filters = $this->filtersForCache();

        return TagAwareCache::remember(
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
                        'id'    => (int) $collection->id,
                        'name'  => (string) $collection->name,
                        'count' => (int) ($countsByCollection[$collection->id] ?? 0),
                    ])
                    ->values()
                    ->all();
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
                        'id'    => (int) $category->id,
                        'name'  => (string) $category->name,
                        'count' => (int) ($countsByCategory[$category->id] ?? 0),
                    ])
                    ->values()
                    ->all();
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
            ->when($this->priceMin !== null, fn (Builder $q): Builder => $q->where('price', '>=', (float) $this->priceMin))
            ->when($this->priceMax !== null, fn (Builder $q): Builder => $q->where('price', '<=', (float) $this->priceMax))
            ->when($this->inStock, fn (Builder $q): Builder => $q->where('stock_quantity', '>', 0))
            ->when($this->onSale, fn (Builder $q): Builder => $q->whereNotNull('sale_price'))
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
     * @return EloquentCollection<int, Category>
     */
    #[Computed]
    public function categories(): EloquentCollection
    {
        $locale = app()->getLocale();
        $filters = $this->filtersForCache();

        return TagAwareCache::remember(
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
                        $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('description', 'like', '%' . $this->search . '%');
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
            },
            $this->tagsForCategoryIndex([
                CacheTags::categories(),
                CacheTags::products(),
            ])
        );
    }

    public function render(): View
    {
        return view('livewire.pages.category.index')
            ->layout('components.layouts.base', [
                'title' => __('categories_index_meta_title'),
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
            'priceMin'              => $this->priceMin,
            'priceMax'              => $this->priceMax,
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
