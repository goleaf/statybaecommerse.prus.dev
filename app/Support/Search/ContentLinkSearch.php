<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Category;
use App\Models\Collection as ProductCollection;
use App\Models\Post;
use App\Models\Product;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Throwable;

final class ContentLinkSearch
{
    /**
     * @return array<int, SearchResult>
     */
    public static function results(string $term, int $limit = 15): array
    {
        $search = trim($term);

        $results = collect();

        $results = $results->merge(self::staticResults($search));

        if ($search !== '') {
            $results = $results
                ->merge(self::productResults($search, $limit))
                ->merge(self::categoryResults($search, $limit))
                ->merge(self::collectionResults($search, $limit))
                ->merge(self::postResults($search, $limit));
        }

        return $results
            ->filter()
            ->unique(fn (SearchResult $result): string => $result->value())
            ->take($limit)
            ->values()
            ->all();
    }

    private static function staticResults(string $term): Collection
    {
        $links = self::staticLinks();
        $needle = Str::lower($term);

        return collect($links)
            ->filter(static function (array $link) use ($needle): bool {
                if ($needle === '') {
                    return true;
                }

                $haystack = Str::lower(trim(sprintf('%s %s %s', $link['label'] ?? '', $link['description'] ?? '', $link['url'] ?? '')));

                return Str::contains($haystack, $needle);
            })
            ->map(static function (array $link): ?SearchResult {
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
            })
            ->filter()
            ->values();
    }

    private static function productResults(string $term, int $limit): Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Product> $products */
        $products = Product::query()
            ->select(['id', 'slug', 'name'])
            ->when($term !== '', static function (Builder $builder) use ($term): void {
                $builder->where(static function (Builder $query) use ($term): void {
                    $query
                        ->where('name->lt', 'like', "%{$term}%")
                        ->orWhere('name->en', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
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

                $result = SearchResult::make($url, $label);
                $result
                    ->withData('type', 'product')
                    ->withData('product_id', $product->getKey())
                    ->withData('slug', $product->getAttribute('slug'));

                return $result;
            })
            ->filter()
            ->values();
    }

    private static function categoryResults(string $term, int $limit): Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Category> $categories */
        $categories = Category::query()
            ->select(['id', 'slug', 'name'])
            ->when($term !== '', static function (Builder $builder) use ($term): void {
                $builder->where(static function (Builder $query) use ($term): void {
                    $query
                        ->where('name->lt', 'like', "%{$term}%")
                        ->orWhere('name->en', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%")
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

                $result = SearchResult::make($url, $label);
                $result
                    ->withData('type', 'category')
                    ->withData('category_id', $category->getKey())
                    ->withData('slug', $category->getAttribute('slug'));

                return $result;
            })
            ->filter()
            ->values();
    }

    private static function collectionResults(string $term, int $limit): Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ProductCollection> $collections */
        $collections = ProductCollection::query()
            ->select(['id', 'slug', 'name'])
            ->when($term !== '', static function (Builder $builder) use ($term): void {
                $builder->where(static function (Builder $query) use ($term): void {
                    $query
                        ->where('name->lt', 'like', "%{$term}%")
                        ->orWhere('name->en', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $collections
            ->map(static function (ProductCollection $collection): ?SearchResult {
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
            })
            ->filter()
            ->values();
    }

    private static function postResults(string $term, int $limit): Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Post> $posts */
        $posts = Post::query()
            ->select(['id', 'slug', 'title', 'title_translations'])
            ->when($term !== '', static function (Builder $builder) use ($term): void {
                $builder->where(static function (Builder $query) use ($term): void {
                    $query
                        ->where('title', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%")
                        ->orWhere('title_translations->lt', 'like', "%{$term}%")
                        ->orWhere('title_translations->en', 'like', "%{$term}%");
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

                $result = SearchResult::make($url, $label);
                $result
                    ->withData('type', 'post')
                    ->withData('post_id', $post->getKey())
                    ->withData('slug', $post->getAttribute('slug'));

                return $result;
            })
            ->filter()
            ->values();
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private static function staticLinks(): array
    {
        $raw = trans('sliders.link_search.static_links');

        if (! is_array($raw)) {
            return [];
        }

        $links = [];

        foreach ($raw as $key => $config) {
            if (! is_array($config)) {
                continue;
            }

            $url = self::resolveStaticUrl($config);

            if ($url === null) {
                continue;
            }

            $links[] = [
                'key'         => is_string($key) ? $key : null,
                'url'         => $url,
                'label'       => is_string($config['label'] ?? null) ? $config['label'] : $url,
                'description' => is_string($config['description'] ?? null) ? $config['description'] : '',
            ];
        }

        return $links;
    }

    private static function resolveStaticUrl(array $config): ?string
    {
        $route = $config['route'] ?? null;
        $url = $config['url'] ?? null;

        if (is_string($route) && $route !== '') {
            $parameters = $config['parameters'] ?? [];

            if (! is_array($parameters)) {
                $parameters = [];
            }

            $generated = self::safeRoute($route, $parameters);

            if ($generated !== null) {
                return $generated;
            }
        }

        if (is_string($url) && $url !== '') {
            return $url;
        }

        return null;
    }

    private static function safeRoute(string $name, mixed $parameters): ?string
    {
        if (! Route::has($name)) {
            return null;
        }

        try {
            $url = route($name, $parameters, false);

            return is_string($url) ? $url : null;
        } catch (Throwable) {
            return null;
        }
    }

    private static function resolveTranslatable(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $locale = app()->getLocale();

            $localized = $value[$locale] ?? collect($value)
                ->filter(static fn ($candidate): bool => is_string($candidate))
                ->first();

            return is_string($localized) ? $localized : '';
        }

        return '';
    }

    private static function typeLabel(string $type): string
    {
        $label = trans("sliders.link_search.types.{$type}");

        if (is_string($label) && $label !== '') {
            return $label;
        }

        return Str::headline($type);
    }
}
