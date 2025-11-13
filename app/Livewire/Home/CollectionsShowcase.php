<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Data\Storefront\Home\CollectionShowcaseItemData;
use App\Models\Collection as ProductCollection;
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

final class CollectionsShowcase extends Component implements HasSchemas
{
    use InteractsWithSchemas;

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

    /**
     * Resolve curated collections for the storefront grid while respecting the
     * underlying cache invalidation hooks for locale-aware payloads.
     */
    #[Computed]
    public function collections(): Collection
    {
        $locale = app()->getLocale();

        $cacheKey = CacheKeys::homeCollections($locale);

        $callback = static function () use ($locale): Collection {
            return ProductCollection::query()
                ->with(['media', 'translations' => function ($q) use ($locale) {
                    $q->where('locale', $locale);
                }])
                ->withCount(['products'])
                ->visible()
                ->active()
                ->ordered()
                ->get()
                ->map(static function (ProductCollection $collection) use ($locale): CollectionShowcaseItemData {
                    // Convert collection models into serialisable DTOs for the cached payload.
                    return CollectionShowcaseItemData::fromModel($collection, $locale);
                });
        };

        $tags = CacheTagHelper::merge(
            CacheTagHelper::collections(),
            CacheTagHelper::locale($locale),
            [CacheTags::home()]
        );

        return TagAwareCache::remember($cacheKey, CacheKeys::TTL_FIVE_MINUTES, $callback, $tags);
    }

    /**
     * Maintain support for property-style access used in older Blade snippets.
     */
    public function getCollectionsProperty(): Collection
    {
        return $this->collections();
    }

    public function collectionsSchema(Schema $schema): Schema
    {
        return $schema->components([
            ViewEntry::make('collections')
                ->label('')
                ->view('livewire.home.partials.collections-grid')
                ->viewData(fn (): array => [
                    'collections' => $this->collections(),
                ]),
        ]);
    }

    public function render(): View
    {
        return view('livewire.home.collections-showcase');
    }
}
