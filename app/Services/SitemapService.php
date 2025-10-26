<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\Seo\LocaleUrlGenerator;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

final class SitemapService
{
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

        $urls = [];

        $urls[] = $this->makeEntry(
            $this->localeUrlGenerator->localizedRoute('localized.home', [], $locale) ?? url('/' . $locale),
            now(),
            'daily',
            1.0,
            $this->localeUrlGenerator->generateAlternates('localized.home', fn (string $loc) => [])
        );

        $this->appendCategoryUrls($urls, $locale);
        $this->appendProductUrls($urls, $locale);
        $this->appendBrandUrls($urls, $locale);

        return array_values(array_filter($urls));
    }

    private function assertLocaleSupported(string $locale): void
    {
        if (! in_array($locale, $this->localeUrlGenerator->supportedLocales(), true)) {
            throw new InvalidArgumentException("Unsupported locale [{$locale}] for sitemap generation.");
        }
    }

    /**
     * @param array<int, array<string, mixed>|null> $urls
     */
    private function appendCategoryUrls(array &$urls, string $locale): void
    {
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
    private function appendProductUrls(array &$urls, string $locale): void
    {
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
}
