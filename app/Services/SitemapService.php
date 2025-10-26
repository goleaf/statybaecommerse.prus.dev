<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Legal;
use App\Models\Product;
use App\Support\Seo\LocaleUrlGenerator;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

final class SitemapService
{
    /**
     * Cache prefix used to memoize locale sitemap payloads.
     */
    private const CACHE_KEY_PREFIX = 'sitemap:urls:';

    /**
     * Number of minutes a generated sitemap should remain cached.
     */
    private const CACHE_TTL_MINUTES = 30;

    public function __construct(
        private readonly LocaleUrlGenerator $localeUrlGenerator
    ) {}

    /**
     * @return array<int, array<string, string>>
     */
    public function getIndex(): array
    {
        return collect($this->localeUrlGenerator->supportedLocales())
            ->map(fn (string $locale) => [
                'loc'     => route('sitemap.locale', ['locale' => $locale]),
                'lastmod' => now()->toAtomString(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLocaleUrls(string $locale): array
    {
        $this->assertLocaleSupported($locale);

        // Cache the expensive sitemap computation to avoid repeatedly traversing
        // large catalog tables while still keeping the data reasonably fresh.
        return Cache::remember(
            $this->buildCacheKey($locale),
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            function () use ($locale): array {
                // Delegate to the generator so that cache invalidation logic can
                // flush a single locale payload without touching others.
                return $this->buildLocaleUrls($locale);
            }
        );
    }

    private function assertLocaleSupported(string $locale): void
    {
        if (! in_array($locale, $this->localeUrlGenerator->supportedLocales(), true)) {
            throw new InvalidArgumentException("Unsupported locale [{$locale}] for sitemap generation.");
        }
    }

    /**
     * Assemble the full list of URLs for a locale without caching concerns.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildLocaleUrls(string $locale): array
    {
        $urls = [];

        // Always include the localized storefront home page as the first entry.
        $urls[] = $this->makeEntry(
            $this->localeUrlGenerator->localizedRoute('localized.home', [], $locale) ?? url('/' . $locale),
            now(),
            'daily',
            1.0,
            $this->localeUrlGenerator->generateAlternates('localized.home', fn (string $loc) => [])
        );

        // Append catalog driven entries in order of importance so crawlers see
        // a consistent priority gradient (categories -> collections -> products -> brands).
        $this->appendCategoryUrls($urls, $locale);
        $this->appendCollectionUrls($urls, $locale);
        $this->appendProductUrls($urls, $locale);
        $this->appendBrandUrls($urls, $locale);
        $this->appendLegalUrls($urls, $locale);

        return array_values(array_filter($urls));
    }

    /**
     * Build a cache key that isolates entries by locale.
     */
    private function buildCacheKey(string $locale): string
    {
        // Normalize locale casing to keep cache lookups consistent.
        return self::CACHE_KEY_PREFIX . strtolower($locale);
    }

    /**
     * @param array<int, array<string, mixed>|null> $urls
     */
    private function appendCategoryUrls(array &$urls, string $locale): void
    {
        // Skip category lookups entirely if the backing table is unavailable in
        // the current test scenario or tenant setup.
        if (! $this->tableExists((new Category())->getTable())) {
            return;
        }

        Category::query()
            ->with('translations')
            ->where('is_active', true)
            ->chunkById(100, function (EloquentCollection $categories) use (&$urls, $locale): void {
                foreach ($categories as $category) {
                    $slug = $this->localeUrlGenerator->translatedValue($category, $locale, 'getTranslatedSlug', 'slug', 'slug');
                    if (! $slug) {
                        continue;
                    }

                    $loc = $this->localeUrlGenerator->localizedRoute('localized.categories.show', ['category' => $slug], $locale);
                    if (! $loc) {
                        continue;
                    }

                    $urls[] = $this->makeEntry(
                        $loc,
                        $category->updated_at,
                        'weekly',
                        0.7,
                        $this->localeUrlGenerator->generateAlternates(
                            'localized.categories.show',
                            fn (string $altLocale) => (
                                ($altSlug = $this->localeUrlGenerator->translatedValue($category, $altLocale, 'getTranslatedSlug', 'slug', 'slug'))
                            ) ? ['category' => $altSlug] : null
                        )
                    );
                }
            });
    }

    /**
     * @param array<int, array<string, mixed>|null> $urls
     */
    private function appendCollectionUrls(array &$urls, string $locale): void
    {
        // Collections may not exist in minimal installations, so guard lookups
        // to keep sitemap generation resilient during migrations and tests.
        if (! $this->tableExists((new Collection())->getTable())) {
            return;
        }

        Collection::query()
            ->with('translations')
            ->chunkById(100, function (EloquentCollection $collections) use (&$urls, $locale): void {
                foreach ($collections as $collection) {
                    $slug = $this->localeUrlGenerator->translatedValue($collection, $locale, 'getTranslatedSlug', 'slug', 'slug');
                    if (! $slug) {
                        continue;
                    }

                    $loc = $this->localeUrlGenerator->localizedRoute('localized.collections.show', ['collection' => $slug], $locale);
                    if (! $loc) {
                        continue;
                    }

                    $urls[] = $this->makeEntry(
                        $loc,
                        $collection->updated_at,
                        'weekly',
                        0.75,
                        $this->localeUrlGenerator->generateAlternates(
                            'localized.collections.show',
                            fn (string $altLocale) => (
                                ($altSlug = $this->localeUrlGenerator->translatedValue($collection, $altLocale, 'getTranslatedSlug', 'slug', 'slug'))
                            ) ? ['collection' => $altSlug] : null
                        )
                    );
                }
            });
    }

    /**
     * @param array<int, array<string, mixed>|null> $urls
     */
    private function appendProductUrls(array &$urls, string $locale): void
    {
        // Bail out quickly if product tables are missing to avoid SQL errors in
        // lightweight testing databases.
        if (! $this->tableExists((new Product())->getTable())) {
            return;
        }

        Product::query()
            ->with(['translations', 'brand.translations'])
            ->where('is_active', true)
            ->where('is_visible', true)
            ->chunkById(100, function (EloquentCollection $products) use (&$urls, $locale): void {
                foreach ($products as $product) {
                    $slug = $this->localeUrlGenerator->translatedValue($product, $locale, 'getTranslatedSlug', 'slug', 'slug');
                    if (! $slug) {
                        continue;
                    }

                    $loc = $this->localeUrlGenerator->localizedRoute('localized.products.show', ['product' => $slug], $locale);
                    if (! $loc) {
                        continue;
                    }

                    $urls[] = $this->makeEntry(
                        $loc,
                        $product->updated_at,
                        'weekly',
                        0.8,
                        $this->localeUrlGenerator->generateAlternates(
                            'localized.products.show',
                            fn (string $altLocale) => (
                                ($altSlug = $this->localeUrlGenerator->translatedValue($product, $altLocale, 'getTranslatedSlug', 'slug', 'slug'))
                            ) ? ['product' => $altSlug] : null
                        )
                    );
                }
            });
    }

    /**
     * @param array<int, array<string, mixed>|null> $urls
     */
    private function appendBrandUrls(array &$urls, string $locale): void
    {
        // Guard against running brand queries when the table does not exist,
        // which can happen in mocked integrations.
        if (! $this->tableExists((new Brand())->getTable())) {
            return;
        }

        Brand::query()
            ->with('translations')
            ->where('is_active', true)
            ->chunkById(100, function (EloquentCollection $brands) use (&$urls, $locale): void {
                foreach ($brands as $brand) {
                    $slug = $this->localeUrlGenerator->translatedValue($brand, $locale, 'getTranslatedSlug', 'slug', 'slug');
                    if (! $slug) {
                        continue;
                    }

                    $loc = $this->localeUrlGenerator->localizedRoute('localized.brands.show', ['slug' => $slug], $locale);
                    if (! $loc) {
                        continue;
                    }

                    $urls[] = $this->makeEntry(
                        $loc,
                        $brand->updated_at,
                        'monthly',
                        0.6,
                        $this->localeUrlGenerator->generateAlternates(
                            'localized.brands.show',
                            fn (string $altLocale) => (
                                ($altSlug = $this->localeUrlGenerator->translatedValue($brand, $altLocale, 'getTranslatedSlug', 'slug', 'slug'))
                            ) ? ['slug' => $altSlug] : null
                        )
                    );
                }
            });
    }

    /**
     * @param array<int, array<string, mixed>|null> $urls
     */
    private function appendLegalUrls(array &$urls, string $locale): void
    {
        // Legal documents are optional in smaller storefront installs, so do
        // nothing when the expected table has not been provisioned yet.
        if (! $this->tableExists((new Legal())->getTable())) {
            return;
        }

        Legal::query()
            ->with('translations')
            ->chunkById(100, function (EloquentCollection $legals) use (&$urls, $locale): void {
                foreach ($legals as $legal) {
                    $slug = $this->localeUrlGenerator->translatedValue($legal, $locale, 'getTranslatedSlug', 'slug', 'key');
                    if (! $slug) {
                        continue;
                    }

                    // Prefer a dedicated localized route when available, then
                    // gracefully fall back to a manual URL if the named route
                    // has not been registered in the current context.
                    $loc = $this->localeUrlGenerator->localizedRoute('localized.legal.show', ['slug' => $slug], $locale)
                        ?? url(sprintf('/%s/legal/%s', $locale, $slug));

                    $alternates = $this->localeUrlGenerator->generateAlternates(
                        'localized.legal.show',
                        fn (string $altLocale) => (
                            ($altSlug = $this->localeUrlGenerator->translatedValue($legal, $altLocale, 'getTranslatedSlug', 'slug', 'key'))
                        ) ? ['slug' => $altSlug] : null
                    );

                    // If no alternates could be generated through routing, fall
                    // back to a basic manual hreflang map using the translated
                    // slugs so crawlers still discover cross-locale content.
                    if ($alternates === []) {
                        $alternates = $this->buildManualAlternateLinks($legal);
                    }

                    $urls[] = $this->makeEntry(
                        $loc,
                        $legal->updated_at,
                        'monthly',
                        0.5,
                        $alternates
                    );
                }
            });
    }

    /**
     * @param array<string, mixed>|null $loc
     * @param array<string, string>     $alternates
     */
    private function makeEntry(?string $loc, ?DateTimeInterface $lastModified, string $changefreq, float $priority, array $alternates = []): ?array
    {
        if (! $loc) {
            return null;
        }

        return [
            'loc'        => $loc,
            'lastmod'    => ($lastModified ?? Carbon::now())->toAtomString(),
            'changefreq' => $changefreq,
            'priority'   => number_format($priority, 1, '.', ''),
            'alternates' => $alternates,
        ];
    }

    /**
     * Build a fallback hreflang map when no localized routes exist for legals.
     *
     * @return array<string, string>
     */
    private function buildManualAlternateLinks(object $legal): array
    {
        $links = [];

        // Iterate all supported locales and synthesize URLs via the locale slug.
        foreach ($this->localeUrlGenerator->supportedLocales() as $altLocale) {
            $altSlug = $this->localeUrlGenerator->translatedValue($legal, $altLocale, 'getTranslatedSlug', 'slug', 'key');
            if (! $altSlug) {
                continue;
            }

            $links[$altLocale] = url(sprintf('/%s/legal/%s', $altLocale, $altSlug));
        }

        if ($links === []) {
            return $links;
        }

        // Ensure x-default is always present to guide search engines toward
        // the primary document when a locale match is unavailable.
        $defaultLocale = config('app.fallback_locale', config('app.locale', 'en'));
        $links['x-default'] ??= $links[$defaultLocale] ?? reset($links);

        return $links;
    }

    private function tableExists(string $table): bool
    {
        // Schema::hasTable gracefully handles databases that cannot introspect
        // during certain operations (e.g., SQLite while migrations are running).
        return Schema::hasTable($table);
    }
}
