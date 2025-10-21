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

    /**
     * @return array<int, array{label: string, description: string, url: string, icon: string}>
     */
    private static function staticLinks(): array
    {
        $links = [
            [
                'route'       => 'home',
                'fallback'    => '/',
                'icon'        => 'heroicon-o-home',
                'label'       => __('admin.content_links.static.home.label'),
                'description' => __('admin.content_links.static.home.description'),
            ],
            [
                'route'       => 'frontend.products.index',
                'fallback'    => '/products',
                'icon'        => 'heroicon-o-rectangle-stack',
                'label'       => __('admin.content_links.static.products.label'),
                'description' => __('admin.content_links.static.products.description'),
            ],
            [
                'route'       => 'frontend.categories.index',
                'fallback'    => '/categories',
                'icon'        => 'heroicon-o-squares-2x2',
                'label'       => __('admin.content_links.static.categories.label'),
                'description' => __('admin.content_links.static.categories.description'),
            ],
            [
                'route'       => 'frontend.collections.index',
                'fallback'    => '/collections',
                'icon'        => 'heroicon-o-collection',
                'label'       => __('admin.content_links.static.collections.label'),
                'description' => __('admin.content_links.static.collections.description'),
            ],
            [
                'route'       => 'frontend.cart.index',
                'fallback'    => '/cart',
                'icon'        => 'heroicon-o-shopping-cart',
                'label'       => __('admin.content_links.static.cart.label'),
                'description' => __('admin.content_links.static.cart.description'),
            ],
            [
                'route'       => 'frontend.checkout.index',
                'fallback'    => '/checkout',
                'icon'        => 'heroicon-o-credit-card',
                'label'       => __('admin.content_links.static.checkout.label'),
                'description' => __('admin.content_links.static.checkout.description'),
            ],
            [
                'route'       => 'frontend.profile.index',
                'fallback'    => '/profile',
                'icon'        => 'heroicon-o-user-circle',
                'label'       => __('admin.content_links.static.account.label'),
                'description' => __('admin.content_links.static.account.description'),
            ],
        ];

        return collect($links)
            ->map(function (array $link): ?array {
                $url = self::resolveUrl($link['route'], $link['fallback']);

                if ($url === null) {
                    return null;
                }

                return [
                    'label'       => $link['label'],
                    'description' => $link['description'],
                    'url'         => $url,
                    'icon'        => $link['icon'],
                ];
            })
            ->filter()
            ->values()
            ->all();
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
            ->map(function (Product $product): ?SearchResult {
                $url = self::resolveProductUrl($product);

                if ($url === null) {
                    return null;
                }

                $label = sprintf('%s · %s', __('admin.content_links.types.product'), self::productLabel($product));
                $result = SearchResult::make($url, $label);

                return $result
                    ->withData('type', 'product')
                    ->withData('icon', 'heroicon-o-cube')
                    ->withData('sku', (string) ($product->getAttribute('sku') ?? ''))
                    ->withData('name', self::resolveTranslatableName($product->getAttribute('name')));
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
            ->map(function (Category $category): ?SearchResult {
                if (! Route::has('frontend.categories.show')) {
                    return null;
                }

                try {
                    $url = route('frontend.categories.show', $category);
                } catch (Throwable $exception) {
                    return null;
                }

                $label = sprintf('%s · %s', __('admin.content_links.types.category'), self::resolveTranslatableName($category->getAttribute('name')));
                $result = SearchResult::make($url, $label);

                return $result
                    ->withData('type', 'category')
                    ->withData('icon', 'heroicon-o-tag');
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
            ->map(function (Collection $collection): ?SearchResult {
                if (! Route::has('frontend.collections.show')) {
                    return null;
                }

                try {
                    $url = route('frontend.collections.show', $collection);
                } catch (Throwable $exception) {
                    return null;
                }

                $label = sprintf('%s · %s', __('admin.content_links.types.collection'), self::resolveTranslatableName($collection->getAttribute('name')));
                $result = SearchResult::make($url, $label);

                return $result
                    ->withData('type', 'collection')
                    ->withData('icon', 'heroicon-o-clipboard-document-list');
            })
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

                $label = sprintf('%s · %s', __('admin.content_links.types.campaign'), (string) $campaign->getAttribute('name'));
                $result = SearchResult::make($url, $label);

                return $result
                    ->withData('type', 'campaign')
                    ->withData('icon', 'heroicon-o-megaphone');
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
            ->map(function (Post $post): ?SearchResult {
                if (! Route::has('frontend.posts.show')) {
                    return null;
                }

                try {
                    $url = route('frontend.posts.show', $post);
                } catch (Throwable $exception) {
                    return null;
                }

                $label = sprintf('%s · %s', __('admin.content_links.types.post'), (string) $post->getAttribute('title'));
                $result = SearchResult::make($url, $label);

                return $result
                    ->withData('type', 'post')
                    ->withData('icon', 'heroicon-o-document-text');
            })
            ->filter()
            ->values()
            ->all();
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
