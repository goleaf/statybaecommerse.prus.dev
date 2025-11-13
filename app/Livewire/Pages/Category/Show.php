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

    public function boot(): void
    {
        // Ensure locale is set from route parameter on every request (including AJAX)
        // This must happen before any translations are used
        $this->ensureLocale();
    }

    public function mount(Category $category): void
    {
        // Ensure locale is set from route parameter
        $this->ensureLocale();

        abort_if(! $category->is_visible, 404);

        if (! $category->relationLoaded('media') || ! $category->relationLoaded('translations')) {
            $category->load(['media', 'translations']);
        }

        $this->category = $category;
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
                ->with([
                    'brand:id,name,slug',
                    'categories:id,name,slug',
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
                    'path' => request()->url(),
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
