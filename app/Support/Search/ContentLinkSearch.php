<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Category;
use App\Models\Collection;
use App\Models\News;
use App\Models\Product;
use App\Support\Seo\LocaleUrlGenerator;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;

final class ContentLinkSearch
{
    private const IDENTIFIER_DELIMITER = ':';

    private const TYPE_STATIC = 'static';

    private const TYPE_PRODUCT = 'product';

    private const TYPE_CATEGORY = 'category';

    private const TYPE_COLLECTION = 'collection';

    private const TYPE_NEWS = 'news';

    /**
     * Static storefront shortcuts used when searching for links.
     *
     * @var array<string, array{label: string, path: string}>
     */
    private const STATIC_LINKS = [
        'home'        => ['label' => 'home', 'path' => '/'],
        'products'    => ['label' => 'products', 'path' => '/products'],
        'collections' => ['label' => 'collections', 'path' => '/collections'],
        'news'        => ['label' => 'news', 'path' => '/news'],
        'contact'     => ['label' => 'contact', 'path' => '/contact'],
    ];

    /**
     * @return array<int, SearchResult>
     */
    public static function sliderLinks(string $term, int $limit = 15): array
    {
        $limitPerType = max(3, (int) ceil($limit / 3));
        $term = trim($term);

        $results = array_merge(
            self::staticLinkResults($term),
            self::productResults($term, $limitPerType),
            self::categoryResults($term, $limitPerType),
            self::collectionResults($term, $limitPerType),
            self::newsResults($term, $limitPerType),
        );

        return array_slice($results, 0, $limit);
    }

    /**
     * Resolve a stored identifier back to its metadata.
     *
     * @return array{identifier: string, url: string, label: string, title: string, type: string}|null
     */
    public static function resolve(string $identifier): ?array
    {
        if ($identifier === '') {
            return null;
        }

        if (! str_contains($identifier, self::IDENTIFIER_DELIMITER)) {
            $url = self::normalisePath($identifier);

            return [
                'identifier' => $identifier,
                'url'        => $url,
                'label'      => $identifier,
                'title'      => $identifier,
                'type'       => 'custom',
            ];
        }

        [$type, $value] = explode(self::IDENTIFIER_DELIMITER, $identifier, 2);

        return match ($type) {
            self::TYPE_STATIC     => self::resolveStatic($value),
            self::TYPE_PRODUCT    => self::resolveProduct((int) $value),
            self::TYPE_CATEGORY   => self::resolveCategory((int) $value),
            self::TYPE_COLLECTION => self::resolveCollection((int) $value),
            self::TYPE_NEWS       => self::resolveNews((int) $value),
            default               => null,
        };
    }

    /**
     * Attempt to resolve an existing URL back to an identifier so the search input can show the current selection.
     *
     * @return array{identifier: string, url: string, label: string, title: string, type: string}|null
     */
    public static function resolveFromUrl(string $url): ?array
    {
        $path = self::normalisePath($url);
        $normalized = self::stripLocalePrefix($path);

        foreach (self::STATIC_LINKS as $key => $link) {
            if ($normalized === $link['path']) {
                return self::resolveStatic($key);
            }
        }

        $slug = self::extractSlug($normalized, '/products/');
        if ($slug !== null && ($resolved = self::resolveProductBySlug($slug))) {
            return $resolved;
        }

        $slug = self::extractSlug($normalized, '/categories/');
        if ($slug !== null && ($resolved = self::resolveCategoryBySlug($slug))) {
            return $resolved;
        }

        $slug = self::extractSlug($normalized, '/collections/');
        if ($slug !== null && ($resolved = self::resolveCollectionBySlug($slug))) {
            return $resolved;
        }

        $slug = self::extractSlug($normalized, '/news/');
        if ($slug !== null && ($resolved = self::resolveNewsBySlug($slug))) {
            return $resolved;
        }

        return null;
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function staticLinkResults(string $term): array
    {
        $typeLabel = self::typeLabel(self::TYPE_STATIC);

        return collect(self::STATIC_LINKS)
            ->filter(function (array $link) use ($term): bool {
                if ($term === '') {
                    return true;
                }

                return Str::contains(Str::lower($link['label']), Str::lower($term));
            })
            ->map(function (array $link, string $key) use ($typeLabel): SearchResult {
                $identifier = self::TYPE_STATIC . self::IDENTIFIER_DELIMITER . $key;
                $title = __('sliders.static_links.' . $link['label']);
                $label = sprintf('%s • %s', $typeLabel, $title);

                return self::makeResult(
                    self::TYPE_STATIC,
                    $identifier,
                    $label,
                    $link['path'],
                    $title,
                );
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function productResults(string $term, int $limit): array
    {
        $locale = app()->getLocale();

        /** @var SupportCollection<int, Product> $products */
        $products = Product::query()
            ->select([
                'products.id',
                'products.slug as base_slug',
                'products.name as base_name',
                'products.sku',
                'product_translations.name as translation_name',
                'product_translations.slug as translation_slug',
            ])
            ->leftJoin('product_translations', static function ($join) use ($locale): void {
                $join->on('product_translations.product_id', '=', 'products.id')
                    ->where('product_translations.locale', '=', $locale);
            })
            ->when($term !== '', static function (Builder $builder) use ($term): void {
                $builder->where(static function (Builder $query) use ($term): void {
                    $query
                        ->where('products.sku', 'like', "%{$term}%")
                        ->orWhere('product_translations.name', 'like', "%{$term}%")
                        ->orWhere('product_translations.slug', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('products.updated_at')
            ->limit($limit)
            ->get();

        return $products
            ->map(static function (Product $product) use ($locale): SearchResult {
                $identifier = self::TYPE_PRODUCT . self::IDENTIFIER_DELIMITER . $product->getKey();
                $name = self::resolveString($product->translation_name ?? null, $product->base_name ?? null);
                $sku = $product->sku ?? '—';
                $label = sprintf('%s • [%s] %s', self::typeLabel(self::TYPE_PRODUCT), $sku, $name);
                $path = self::productPath($product, $locale);

                return self::makeResult(
                    self::TYPE_PRODUCT,
                    $identifier,
                    $label,
                    $path,
                    $name,
                );
            })
            ->all();
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function categoryResults(string $term, int $limit): array
    {
        $locale = app()->getLocale();

        /** @var SupportCollection<int, Category> $categories */
        $categories = Category::query()
            ->select([
                'categories.id',
                'category_translations.name as translation_name',
                'category_translations.slug as translation_slug',
            ])
            ->leftJoin('category_translations', static function ($join) use ($locale): void {
                $join->on('category_translations.category_id', '=', 'categories.id')
                    ->where('category_translations.locale', '=', $locale);
            })
            ->when($term !== '', static function (Builder $builder) use ($term): void {
                $builder->where(static function (Builder $query) use ($term): void {
                    $query
                        ->where('category_translations.name', 'like', "%{$term}%")
                        ->orWhere('category_translations.slug', 'like', "%{$term}%");
                });
            })
            ->orderBy('category_translations.name')
            ->limit($limit)
            ->get();

        return $categories
            ->map(static function (Category $category) use ($locale): SearchResult {
                $identifier = self::TYPE_CATEGORY . self::IDENTIFIER_DELIMITER . $category->getKey();
                $name = self::resolveString($category->translation_name ?? null, '');
                $label = sprintf('%s • %s', self::typeLabel(self::TYPE_CATEGORY), $name);
                $path = self::categoryPath($category, $locale);

                return self::makeResult(
                    self::TYPE_CATEGORY,
                    $identifier,
                    $label,
                    $path,
                    $name,
                );
            })
            ->all();
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function collectionResults(string $term, int $limit): array
    {
        $locale = app()->getLocale();

        /** @var SupportCollection<int, Collection> $collections */
        $collections = Collection::query()
            ->select([
                'collections.id',
                'collection_translations.name as translation_name',
                'collection_translations.slug as translation_slug',
            ])
            ->leftJoin('collection_translations', static function ($join) use ($locale): void {
                $join->on('collection_translations.collection_id', '=', 'collections.id')
                    ->where('collection_translations.locale', '=', $locale);
            })
            ->when($term !== '', static function (Builder $builder) use ($term): void {
                $builder->where(static function (Builder $query) use ($term): void {
                    $query
                        ->where('collection_translations.name', 'like', "%{$term}%")
                        ->orWhere('collection_translations.slug', 'like', "%{$term}%");
                });
            })
            ->orderBy('collection_translations.name')
            ->limit($limit)
            ->get();

        return $collections
            ->map(static function (Collection $collection) use ($locale): SearchResult {
                $identifier = self::TYPE_COLLECTION . self::IDENTIFIER_DELIMITER . $collection->getKey();
                $name = self::resolveString($collection->translation_name ?? null, '');
                $label = sprintf('%s • %s', self::typeLabel(self::TYPE_COLLECTION), $name);
                $path = self::collectionPath($collection, $locale);

                return self::makeResult(
                    self::TYPE_COLLECTION,
                    $identifier,
                    $label,
                    $path,
                    $name,
                );
            })
            ->all();
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function newsResults(string $term, int $limit): array
    {
        $locale = app()->getLocale();

        /** @var SupportCollection<int, News> $articles */
        $articles = News::query()
            ->select([
                'news.id',
                'news_translations.title as translation_title',
                'news_translations.slug as translation_slug',
            ])
            ->leftJoin('news_translations', static function ($join) use ($locale): void {
                $join->on('news_translations.news_id', '=', 'news.id')
                    ->where('news_translations.locale', '=', $locale);
            })
            ->when($term !== '', static function (Builder $builder) use ($term): void {
                $builder->where(static function (Builder $query) use ($term): void {
                    $query
                        ->where('news_translations.title', 'like', "%{$term}%")
                        ->orWhere('news_translations.slug', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('news.published_at')
            ->limit($limit)
            ->get();

        return $articles
            ->map(static function (News $article) use ($locale): SearchResult {
                $identifier = self::TYPE_NEWS . self::IDENTIFIER_DELIMITER . $article->getKey();
                $title = self::resolveString($article->translation_title ?? null, '');
                $label = sprintf('%s • %s', self::typeLabel(self::TYPE_NEWS), $title);
                $path = self::newsPath($article, $locale);

                return self::makeResult(
                    self::TYPE_NEWS,
                    $identifier,
                    $label,
                    $path,
                    $title,
                );
            })
            ->all();
    }

    /**
     * @return array{identifier: string, url: string, label: string, title: string, type: string}|null
     */
    private static function resolveStatic(string $key): ?array
    {
        if (! array_key_exists($key, self::STATIC_LINKS)) {
            return null;
        }

        $link = self::STATIC_LINKS[$key];
        $identifier = self::TYPE_STATIC . self::IDENTIFIER_DELIMITER . $key;
        $title = __('sliders.static_links.' . $link['label']);
        $label = sprintf('%s • %s', self::typeLabel(self::TYPE_STATIC), $title);

        return [
            'identifier' => $identifier,
            'url'        => $link['path'],
            'label'      => $label,
            'title'      => $title,
            'type'       => self::TYPE_STATIC,
        ];
    }

    /**
     * @return array{identifier: string, url: string, label: string, title: string, type: string}|null
     */
    private static function resolveProduct(int $id): ?array
    {
        $locale = app()->getLocale();

        $product = Product::query()
            ->select([
                'products.id',
                'products.slug as base_slug',
                'products.name as base_name',
                'products.sku',
                'product_translations.name as translation_name',
                'product_translations.slug as translation_slug',
            ])
            ->leftJoin('product_translations', static function ($join) use ($locale): void {
                $join->on('product_translations.product_id', '=', 'products.id')
                    ->where('product_translations.locale', '=', $locale);
            })
            ->where('products.id', $id)
            ->first();

        if (! $product instanceof Product) {
            return null;
        }

        $name = self::resolveString($product->translation_name ?? null, $product->base_name ?? null);
        $sku = $product->sku ?? '—';
        $label = sprintf('%s • [%s] %s', self::typeLabel(self::TYPE_PRODUCT), $sku, $name);
        $path = self::productPath($product, $locale);

        return [
            'identifier' => self::TYPE_PRODUCT . self::IDENTIFIER_DELIMITER . $product->getKey(),
            'url'        => $path,
            'label'      => $label,
            'title'      => $name,
            'type'       => self::TYPE_PRODUCT,
        ];
    }

    /**
     * @return array{identifier: string, url: string, label: string, title: string, type: string}|null
     */
    private static function resolveCategory(int $id): ?array
    {
        $locale = app()->getLocale();

        $category = Category::query()
            ->select([
                'categories.id',
                'category_translations.name as translation_name',
                'category_translations.slug as translation_slug',
            ])
            ->leftJoin('category_translations', static function ($join) use ($locale): void {
                $join->on('category_translations.category_id', '=', 'categories.id')
                    ->where('category_translations.locale', '=', $locale);
            })
            ->where('categories.id', $id)
            ->first();

        if (! $category instanceof Category) {
            return null;
        }

        $name = self::resolveString($category->translation_name ?? null, '');
        $label = sprintf('%s • %s', self::typeLabel(self::TYPE_CATEGORY), $name);
        $path = self::categoryPath($category, $locale);

        return [
            'identifier' => self::TYPE_CATEGORY . self::IDENTIFIER_DELIMITER . $category->getKey(),
            'url'        => $path,
            'label'      => $label,
            'title'      => $name,
            'type'       => self::TYPE_CATEGORY,
        ];
    }

    /**
     * @return array{identifier: string, url: string, label: string, title: string, type: string}|null
     */
    private static function resolveCollection(int $id): ?array
    {
        $locale = app()->getLocale();

        $collection = Collection::query()
            ->select([
                'collections.id',
                'collection_translations.name as translation_name',
                'collection_translations.slug as translation_slug',
            ])
            ->leftJoin('collection_translations', static function ($join) use ($locale): void {
                $join->on('collection_translations.collection_id', '=', 'collections.id')
                    ->where('collection_translations.locale', '=', $locale);
            })
            ->where('collections.id', $id)
            ->first();

        if (! $collection instanceof Collection) {
            return null;
        }

        $name = self::resolveString($collection->translation_name ?? null, '');
        $label = sprintf('%s • %s', self::typeLabel(self::TYPE_COLLECTION), $name);
        $path = self::collectionPath($collection, $locale);

        return [
            'identifier' => self::TYPE_COLLECTION . self::IDENTIFIER_DELIMITER . $collection->getKey(),
            'url'        => $path,
            'label'      => $label,
            'title'      => $name,
            'type'       => self::TYPE_COLLECTION,
        ];
    }

    /**
     * @return array{identifier: string, url: string, label: string, title: string, type: string}|null
     */
    private static function resolveNews(int $id): ?array
    {
        $locale = app()->getLocale();

        $article = News::query()
            ->select([
                'news.id',
                'news_translations.title as translation_title',
                'news_translations.slug as translation_slug',
            ])
            ->leftJoin('news_translations', static function ($join) use ($locale): void {
                $join->on('news_translations.news_id', '=', 'news.id')
                    ->where('news_translations.locale', '=', $locale);
            })
            ->where('news.id', $id)
            ->first();

        if (! $article instanceof News) {
            return null;
        }

        $title = self::resolveString($article->translation_title ?? null, '');
        $label = sprintf('%s • %s', self::typeLabel(self::TYPE_NEWS), $title);
        $path = self::newsPath($article, $locale);

        return [
            'identifier' => self::TYPE_NEWS . self::IDENTIFIER_DELIMITER . $article->getKey(),
            'url'        => $path,
            'label'      => $label,
            'title'      => $title,
            'type'       => self::TYPE_NEWS,
        ];
    }

    /**
     * @return array{identifier: string, url: string, label: string, title: string, type: string}|null
     */
    private static function resolveProductBySlug(string $slug): ?array
    {
        $locale = app()->getLocale();

        $product = Product::query()
            ->select([
                'products.id',
                'products.slug as base_slug',
                'products.name as base_name',
                'products.sku',
                'product_translations.name as translation_name',
                'product_translations.slug as translation_slug',
            ])
            ->leftJoin('product_translations', static function ($join) use ($locale): void {
                $join->on('product_translations.product_id', '=', 'products.id')
                    ->where('product_translations.locale', '=', $locale);
            })
            ->where('product_translations.slug', $slug)
            ->orWhere('products.slug', $slug)
            ->first();

        if (! $product instanceof Product) {
            return null;
        }

        return self::resolveProduct((int) $product->getKey());
    }

    /**
     * @return array{identifier: string, url: string, label: string, title: string, type: string}|null
     */
    private static function resolveCategoryBySlug(string $slug): ?array
    {
        $locale = app()->getLocale();

        $category = Category::query()
            ->select([
                'categories.id',
                'category_translations.slug as translation_slug',
            ])
            ->leftJoin('category_translations', static function ($join) use ($locale): void {
                $join->on('category_translations.category_id', '=', 'categories.id')
                    ->where('category_translations.locale', '=', $locale);
            })
            ->where('category_translations.slug', $slug)
            ->first();

        if (! $category instanceof Category) {
            return null;
        }

        return self::resolveCategory((int) $category->getKey());
    }

    /**
     * @return array{identifier: string, url: string, label: string, title: string, type: string}|null
     */
    private static function resolveCollectionBySlug(string $slug): ?array
    {
        $locale = app()->getLocale();

        $collection = Collection::query()
            ->select([
                'collections.id',
                'collection_translations.slug as translation_slug',
            ])
            ->leftJoin('collection_translations', static function ($join) use ($locale): void {
                $join->on('collection_translations.collection_id', '=', 'collections.id')
                    ->where('collection_translations.locale', '=', $locale);
            })
            ->where('collection_translations.slug', $slug)
            ->first();

        if (! $collection instanceof Collection) {
            return null;
        }

        return self::resolveCollection((int) $collection->getKey());
    }

    /**
     * @return array{identifier: string, url: string, label: string, title: string, type: string}|null
     */
    private static function resolveNewsBySlug(string $slug): ?array
    {
        $locale = app()->getLocale();

        $article = News::query()
            ->select([
                'news.id',
                'news_translations.slug as translation_slug',
            ])
            ->leftJoin('news_translations', static function ($join) use ($locale): void {
                $join->on('news_translations.news_id', '=', 'news.id')
                    ->where('news_translations.locale', '=', $locale);
            })
            ->where('news_translations.slug', $slug)
            ->first();

        if (! $article instanceof News) {
            return null;
        }

        return self::resolveNews((int) $article->getKey());
    }

    private static function typeLabel(string $type): string
    {
        return __('sliders.link_types.' . $type);
    }

    private static function resolveString(?string $primary, ?string $fallback): string
    {
        if (is_string($primary) && trim($primary) !== '') {
            return $primary;
        }

        if (is_string($fallback) && trim($fallback) !== '') {
            return $fallback;
        }

        return __('sliders.unknown_title');
    }

    private static function productPath(Product $product, string $locale): string
    {
        $slug = self::resolveString($product->translation_slug ?? null, $product->base_slug ?? null);
        $url = app(LocaleUrlGenerator::class)->localizedRoute('localized.products.show', ['product' => $slug], $locale);

        return self::normalisePath($url ?? '/products/' . $slug);
    }

    private static function categoryPath(Category $category, string $locale): string
    {
        $slug = self::resolveString($category->translation_slug ?? null, null);
        $url = app(LocaleUrlGenerator::class)->localizedRoute('localized.categories.show', ['category' => $slug], $locale);

        return self::normalisePath($url ?? '/categories/' . $slug);
    }

    private static function collectionPath(Collection $collection, string $locale): string
    {
        $slug = self::resolveString($collection->translation_slug ?? null, null);
        $url = app(LocaleUrlGenerator::class)->localizedRoute('localized.collections.show', ['collection' => $slug], $locale);

        return self::normalisePath($url ?? '/collections/' . $slug);
    }

    private static function newsPath(News $article, string $locale): string
    {
        $slug = self::resolveString($article->translation_slug ?? null, null);
        $url = app(LocaleUrlGenerator::class)->localizedRoute('localized.news.show', ['slug' => $slug], $locale);

        return self::normalisePath($url ?? '/news/' . $slug);
    }

    private static function normalisePath(?string $url): string
    {
        if ($url === null || $url === '') {
            return '/';
        }

        $parts = parse_url($url);
        $path = $parts['path'] ?? '/';

        if ($path === '') {
            $path = '/';
        }

        return Str::startsWith($path, '/') ? $path : '/' . $path;
    }

    private static function stripLocalePrefix(string $path): string
    {
        $segments = array_values(array_filter(explode('/', ltrim($path, '/'))));
        $locales = app(LocaleUrlGenerator::class)->supportedLocales();

        if ($segments !== [] && in_array($segments[0], $locales, true)) {
            array_shift($segments);
        }

        return $segments === [] ? '/' : '/' . implode('/', $segments);
    }

    private static function extractSlug(string $path, string $prefix): ?string
    {
        if (! Str::startsWith($path, $prefix)) {
            return null;
        }

        $slug = Str::after($path, $prefix);
        $slug = trim($slug, '/');

        return $slug !== '' ? $slug : null;
    }

    private static function makeResult(string $type, string $identifier, string $label, string $path, ?string $title = null): SearchResult
    {
        $result = SearchResult::make($identifier, $label);

        $result
            ->withData('type', $type)
            ->withData('url', $path)
            ->withData('title', $title ?? $label)
            ->withData('identifier', $identifier);

        return $result;
    }
}
