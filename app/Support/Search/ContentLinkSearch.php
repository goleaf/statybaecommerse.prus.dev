<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Campaign;
use App\Models\Category;
use App\Models\Collection;
use App\Models\News;
use App\Models\Post;
use App\Models\Product;
use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Throwable;

final class ContentLinkSearch
{
    /**
     * Build a combined list of internal content links that can be suggested when selecting slider links.
     *
     * @return array<int, SearchResult>
     */
    public static function suggest(string $term, int $limit = 15): array
    {
        $results = collect();

        $results = $results->merge(self::staticResults($term));

        $remaining = $limit - $results->count();
        if ($remaining <= 0) {
            return $results->take($limit)->values()->all();
        }

        foreach ([
            fn (int $l): array => self::productResults($term, $l),
            fn (int $l): array => self::categoryResults($term, $l),
            fn (int $l): array => self::collectionResults($term, $l),
            fn (int $l): array => self::campaignResults($term, $l),
            fn (int $l): array => self::postResults($term, $l),
            fn (int $l): array => self::newsResults($term, $l),
        ] as $builder) {
            $portion = $builder($remaining);

            if ($portion === []) {
                continue;
            }

            $results = $results->merge($portion);
            $remaining = $limit - $results->count();

            if ($remaining <= 0) {
                break;
            }
        }

        return $results->take($limit)->values()->all();
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function staticResults(string $term): array
    {
        $term = Str::lower(trim($term));

        return collect(self::staticLinks())
            ->filter(function (array $link) use ($term): bool {
                if ($term === '') {
                    return true;
                }

                $haystack = Str::lower($link['label'] . ' ' . $link['description']);

                return Str::contains($haystack, $term);
            })
            ->map(function (array $link): SearchResult {
                $result = SearchResult::make($link['url'], $link['label']);

                return $result
                    ->withData('type', 'static')
                    ->withData('icon', $link['icon'])
                    ->withData('description', $link['description']);
            })
            ->values()
            ->all();
    }

    public static function hydrateComponent(SearchableInput $component, ?string $state): void
    {
        if (! is_string($state) || $state === '') {
            SearchableComponentHelper::forget($component);

            return;
        }

        $result = self::resolveForValue($state) ?? SearchResult::make($state, $state);

        SearchableComponentHelper::apply($component, $result);
    }
    private static function resolveUrl(string $route, string $fallback): ?string
    {
        if (Route::has($route)) {
            try {
                return route($route);
            } catch (Throwable $exception) {
                // Fallback below.
            }
        }

        $url = url($fallback);

        return is_string($url) ? $url : null;
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function productResults(string $term, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        /** @var EloquentCollection<int, Product> $products */
        $products = Product::query()
            ->select(['id', 'name', 'slug', 'sku'])
            ->when(trim($term) !== '', function (Builder $builder) use ($term): void {
                $builder->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('name->en', 'like', "%{$term}%")
                        ->orWhere('name->lt', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();

        return $products
            ->map(static function (Product $product): ?SearchResult {
                $url = self::safeRoute('frontend.products.show', $product);

                if ($url === null) {
                    return null;
                }

                $name = self::resolveTranslatable($product->getAttribute('name'));
                $label = trim(sprintf('%s • %s', self::typeLabel('product'), $name !== '' ? $name : (string) $product->getAttribute('slug')));

                // Record the product identifiers required to resolve storefront URLs.
                return SearchResultPayload::normalise(SearchResult::make($url, $label), [
                    'type'       => 'product',
                    'product_id' => $product->getKey(),
                    'slug'       => $product->getAttribute('slug'),
                ]);
            })
            ->filter()
            ->values()
            ->all();
    }

    private static function resolveProductUrl(Product $product): ?string
    {
        if (! Route::has('frontend.products.show')) {
            return null;
        }

        try {
            return route('frontend.products.show', $product);
        } catch (Throwable $exception) {
            return null;
        }
    }

    private static function productLabel(Product $product): string
    {
        $sku = $product->getAttribute('sku');
        $name = self::resolveTranslatableName($product->getAttribute('name'));

        return trim(sprintf('[%s] %s', $sku ?: '—', $name));
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function categoryResults(string $term, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        /** @var EloquentCollection<int, Category> $categories */
        $categories = Category::query()
            ->select(['id', 'name', 'slug'])
            ->when(trim($term) !== '', function (Builder $builder) use ($term): void {
                $builder->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('name->en', 'like', "%{$term}%")
                        ->orWhere('name->lt', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $categories
            ->map(static function (Category $category): ?SearchResult {
                $url = self::safeRoute('frontend.categories.show', $category);

                if ($url === null) {
                    return null;
                }

                $name = self::resolveTranslatable($category->getAttribute('name'));
                $label = trim(sprintf('%s • %s', self::typeLabel('category'), $name !== '' ? $name : (string) $category->getAttribute('slug')));

                // Bundle the category identifiers alongside the friendly label.
                return SearchResultPayload::normalise(SearchResult::make($url, $label), [
                    'type'        => 'category',
                    'category_id' => $category->getKey(),
                    'slug'        => $category->getAttribute('slug'),
                ]);
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function collectionResults(string $term, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        /** @var EloquentCollection<int, Collection> $collections */
        $collections = Collection::query()
            ->select(['id', 'name', 'slug'])
            ->when(trim($term) !== '', function (Builder $builder) use ($term): void {
                $builder->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('name->en', 'like', "%{$term}%")
                        ->orWhere('name->lt', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $collections
            ->map(static fn (ProductCollection $collection): ?SearchResult => self::collectionResult($collection))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function campaignResults(string $term, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        /** @var EloquentCollection<int, Campaign> $campaigns */
        $campaigns = Campaign::query()
            ->select(['id', 'name', 'slug'])
            ->when(trim($term) !== '', function (Builder $builder) use ($term): void {
                $builder->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('starts_at')
            ->limit($limit)
            ->get();

        return $campaigns
            ->map(function (Campaign $campaign): ?SearchResult {
                if (! Route::has('frontend.campaigns.show')) {
                    return null;
                }

                try {
                    $url = route('frontend.campaigns.show', $campaign);
                } catch (Throwable $exception) {
                    return null;
                }

                // Keep the collection context in the payload so the form remembers selections.
                return SearchResultPayload::normalise(SearchResult::make($url, $label), [
                    'type'          => 'collection',
                    'collection_id' => $collection->getKey(),
                    'slug'          => $collection->getAttribute('slug'),
                ]);
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function postResults(string $term, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        /** @var EloquentCollection<int, Post> $posts */
        $posts = Post::query()
            ->select(['id', 'title', 'slug'])
            ->when(trim($term) !== '', function (Builder $builder) use ($term): void {
                $builder->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('title', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();

        return $posts
            ->map(static function (Post $post): ?SearchResult {
                $url = self::safeRoute('frontend.posts.show', $post);

                if ($url === null) {
                    return null;
                }

                $title = self::resolveTranslatable($post->getAttribute('title'));
                if ($title === '') {
                    /** @var array<string, mixed>|null $translations */
                    $translations = $post->getAttribute('title_translations');
                    if (is_array($translations)) {
                        $title = self::resolveTranslatable($translations);
                    }
                }

                $label = trim(sprintf('%s • %s', self::typeLabel('post'), $title !== '' ? $title : (string) $post->getAttribute('slug')));

                // Persist the blog metadata so preview links can render without extra lookups.
                return SearchResultPayload::normalise(SearchResult::make($url, $label), [
                    'type'    => 'post',
                    'post_id' => $post->getKey(),
                    'slug'    => $post->getAttribute('slug'),
                ]);
            })
            ->filter()
            ->values()
            ->all();
    }

    private static function staticResult(array $link): ?SearchResult
    {
        $url = $link['url'] ?? null;

        if (! is_string($url) || $url === '') {
            return null;
        }

        $label = is_string($link['label'] ?? null) ? $link['label'] : $url;
        $description = is_string($link['description'] ?? null) ? $link['description'] : '';
        $key = is_string($link['key'] ?? null) ? $link['key'] : null;

        $result = SearchResult::make($url, $label);
        $result
            ->withData('type', 'static')
            ->withData('description', $description);

        if ($key !== null) {
            $result->withData('key', $key);
        }

        return $result;
    }

    private static function productResult(Product $product): ?SearchResult
    {
        $url = self::safeRoute('frontend.products.show', $product);

        if ($url === null) {
            return null;
        }

        $name = self::resolveTranslatable($product->getAttribute('name'));
        $label = trim(sprintf('%s • %s', self::typeLabel('product'), $name !== '' ? $name : (string) $product->getAttribute('slug')));

        $result = SearchResult::make($url, $label);
        $result
            ->withData('type', 'product')
            ->withData('product_id', $product->getKey())
            ->withData('slug', $product->getAttribute('slug'));

        return $result;
    }

    private static function categoryResult(Category $category): ?SearchResult
    {
        $url = self::safeRoute('frontend.categories.show', $category);

        if ($url === null) {
            return null;
        }

        $name = self::resolveTranslatable($category->getAttribute('name'));
        $label = trim(sprintf('%s • %s', self::typeLabel('category'), $name !== '' ? $name : (string) $category->getAttribute('slug')));

        $result = SearchResult::make($url, $label);
        $result
            ->withData('type', 'category')
            ->withData('category_id', $category->getKey())
            ->withData('slug', $category->getAttribute('slug'));

        return $result;
    }

    private static function collectionResult(ProductCollection $collection): ?SearchResult
    {
        $url = self::safeRoute('frontend.collections.show', $collection);

        if ($url === null) {
            return null;
        }

        $name = self::resolveTranslatable($collection->getAttribute('name'));
        $label = trim(sprintf('%s • %s', self::typeLabel('collection'), $name !== '' ? $name : (string) $collection->getAttribute('slug')));

        $result = SearchResult::make($url, $label);
        $result
            ->withData('type', 'collection')
            ->withData('collection_id', $collection->getKey())
            ->withData('slug', $collection->getAttribute('slug'));

        return $result;
    }

    private static function postResult(Post $post): ?SearchResult
    {
        $url = self::safeRoute('frontend.posts.show', $post);

        if ($url === null) {
            return null;
        }

        $title = self::resolveTranslatable($post->getAttribute('title'));

        if ($title === '') {
            /** @var array<string, mixed>|null $translations */
            $translations = $post->getAttribute('title_translations');

            if (is_array($translations)) {
                $title = self::resolveTranslatable($translations);
            }
        }

        $label = trim(sprintf('%s • %s', self::typeLabel('post'), $title !== '' ? $title : (string) $post->getAttribute('slug')));

        $result = SearchResult::make($url, $label);
        $result
            ->withData('type', 'post')
            ->withData('post_id', $post->getKey())
            ->withData('slug', $post->getAttribute('slug'));

        return $result;
    }

    private static function resolveForValue(string $value): ?SearchResult
    {
        $static = collect(self::staticLinks())->firstWhere('url', $value);

        if (is_array($static)) {
            return self::staticResult($static);
        }

        $path = parse_url($value, PHP_URL_PATH);
        $path ??= $value;

        $segments = array_values(array_filter(explode('/', trim((string) $path, '/'))));

        if ($segments === []) {
            return null;
        }

        $slug = $segments[1] ?? null;

        if (! is_string($slug) || $slug === '') {
            return null;
        }

        return match ($segments[0]) {
            'products'    => self::resolveProductBySlug($slug),
            'categories'  => self::resolveCategoryBySlug($slug),
            'collections' => self::resolveCollectionBySlug($slug),
            'posts'       => self::resolvePostBySlug($slug),
            default       => null,
        };
    }

    private static function resolveProductBySlug(string $slug): ?SearchResult
    {
        $product = Product::query()
            ->select(['id', 'slug', 'name'])
            ->where('slug', $slug)
            ->first();

        return $product instanceof Product ? self::productResult($product) : null;
    }

    private static function resolveCategoryBySlug(string $slug): ?SearchResult
    {
        $category = Category::query()
            ->select(['id', 'slug', 'name'])
            ->where('slug', $slug)
            ->first();

        return $category instanceof Category ? self::categoryResult($category) : null;
    }

    private static function resolveCollectionBySlug(string $slug): ?SearchResult
    {
        $collection = ProductCollection::query()
            ->select(['id', 'slug', 'name'])
            ->where('slug', $slug)
            ->first();

        return $collection instanceof ProductCollection ? self::collectionResult($collection) : null;
    }

    private static function resolvePostBySlug(string $slug): ?SearchResult
    {
        $post = Post::query()
            ->select(['id', 'slug', 'title', 'title_translations'])
            ->where('slug', $slug)
            ->first();

        return $post instanceof Post ? self::postResult($post) : null;
    }

    /**
     * @return array<int, SearchResult>
     */
    private static function newsResults(string $term, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        /** @var EloquentCollection<int, News> $newsItems */
        $newsItems = News::query()
            ->select(['id', 'title', 'slug'])
            ->when(trim($term) !== '', function (Builder $builder) use ($term): void {
                $builder->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('title', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();

        return $newsItems
            ->map(function (News $news): ?SearchResult {
                if (! Route::has('frontend.news.show')) {
                    return null;
                }

                try {
                    $url = route('frontend.news.show', $news);
                } catch (Throwable $exception) {
                    return null;
                }

                $label = sprintf('%s · %s', __('admin.content_links.types.news'), (string) $news->getAttribute('title'));
                $result = SearchResult::make($url, $label);

                return $result
                    ->withData('type', 'news')
                    ->withData('icon', 'heroicon-o-newspaper');
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Resolve translated or plain string names stored as arrays.
     */
    private static function resolveTranslatableName(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $locale = app()->getLocale();
            $translated = $value[$locale] ?? reset($value);

            return is_string($translated) ? $translated : '';
        }

        return '';
    }
}
