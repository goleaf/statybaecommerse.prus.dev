<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Data\Storefront\Home\ProductListItemData;
use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use App\Support\Cache\CacheTags;
use App\Support\Cache\TagAwareCache;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class ProductShelf extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    use WithCart;
    use WithNotifications;

    public string $preset = 'featured';

    public string $title = '';

    public ?string $subtitle = null;

    public int $limit = 8;

    public function boot(): void
    {
        // Ensure locale is set from route parameter on every request (including AJAX)
        // This must happen before any translations are used
        $this->ensureLocale();
    }

    public function mount(string $preset = 'featured', string $title = '', ?string $subtitle = null, int $limit = 8): void
    {
        // Ensure locale is set from route parameter
        $this->ensureLocale();
        
        $this->preset = $preset;
        $this->limit = max(4, $limit);

        $sectionKey = in_array($this->preset, ['latest', 'sale', 'trending', 'featured'], true)
            ? $this->preset
            : 'featured';

        $titleMap = [
            'featured' => 'home_products_featured_title',
            'latest' => 'home_products_latest_title',
            'trending' => 'home_products_trending_title',
            'sale' => 'home_products_sale_title',
        ];
        
        $subtitleMap = [
            'featured' => 'home_products_featured_subtitle',
            'latest' => 'home_products_latest_subtitle',
            'trending' => 'home_products_trending_subtitle',
            'sale' => 'home_products_sale_subtitle',
        ];

        $this->title = $title !== ''
            ? $title
            : __($titleMap[$sectionKey] ?? 'home_products_featured_title');

        $this->subtitle = $subtitle ?? __($subtitleMap[$sectionKey] ?? 'home_products_featured_subtitle');
    }

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

    #[Computed]
    public function products(): Collection
    {
        $locale = app()->getLocale();
        $cacheKey = CacheKeys::homeShelf($this->preset, $this->limit, $locale);

        $callback = function () use ($locale): Collection {
            $query = Product::query()
                ->with(['brand', 'media', 'categories'])
                ->with(['translations' => function ($q) use ($locale) {
                    $q->where('locale', $locale);
                }, 'categories.translations' => function ($q) use ($locale) {
                    $q->where('locale', $locale);
                }])
                ->withAvg(['reviews as average_rating' => fn ($q) => $q->where('is_approved', true)], 'rating')
                ->withCount(['reviews' => fn ($q) => $q->where('is_approved', true)])
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereNull('deleted_at');

            $query = match ($this->preset) {
                'latest' => $query->orderByDesc('published_at'),
                'sale'   => $query
                    ->where(function ($saleQuery): void {
                        $saleQuery
                            ->whereNotNull('sale_price')
                            ->whereColumn('sale_price', '<', 'price')
                            ->orWhere(function ($compareQuery): void {
                                $compareQuery
                                    ->whereNotNull('compare_price')
                                    ->whereColumn('compare_price', '>', 'price');
                            });
                    })
                    ->orderByDesc('updated_at')
                    ->orderByDesc('published_at'),
                'trending' => $query
                    ->withSum('orderItems as orders_quantity', 'quantity')
                    ->orderByDesc('orders_quantity')
                    ->orderByDesc('reviews_count')
                    ->orderByDesc('published_at'),
                default => $query
                    ->where('is_featured', true)
                    ->orderBy('sort_order')
                    ->orderByDesc('published_at'),
            };

            return $query->limit($this->limit)
                ->get()
                ->map(static function (Product $product) use ($locale): ProductListItemData {
                    // Convert Eloquent models into cached DTOs so the view works with serialisable payloads.
                    return ProductListItemData::fromModel($product, $locale);
                });
        };

        $tags = CacheTagHelper::merge(
            CacheTagHelper::products(),
            CacheTagHelper::locale($locale),
            [CacheTags::home()]
        );

        return TagAwareCache::remember($cacheKey, CacheKeys::TTL_MINUTE, $callback, $tags);
    }

    public function productShelf(Schema $schema): Schema
    {
        return $schema->components([
            ViewEntry::make('products')
                ->label('')
                ->view('livewire.home.partials.product-shelf')
                ->viewData(fn (): array => [
                    'products' => $this->products(),
                    'title'    => $this->title,
                    'subtitle' => $this->subtitle,
                    'preset'   => $this->preset,
                ]),
        ]);
    }

    public function render(): View
    {
        return view('livewire.home.product-shelf');
    }
}
