<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Collection;
use App\Models\Post;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

final class ContentLinkSearch
{
    /**
     * @return array<int, SearchResult>
     */
    public static function suggestions(string $term, int $limit = 10): array
    {
        $normalized = trim($term);
        $locale = app()->getLocale();
        $perType = max(1, (int) ceil($limit / 2));

        /** @var \Illuminate\Support\Collection<int, SearchResult> $postResults */
        $postResults = self::postQuery($normalized)
            ->limit($perType)
            ->get()
            ->map(fn (Post $post): SearchResult => self::buildPostResult($post, $locale));

        /** @var \Illuminate\Support\Collection<int, SearchResult> $collectionResults */
        $collectionResults = self::collectionQuery($normalized)
            ->limit($perType)
            ->get()
            ->map(fn (Collection $collection): SearchResult => self::buildCollectionResult($collection, $locale));

        return $postResults
            ->merge($collectionResults)
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return Builder<Post>
     */
    private static function postQuery(string $term): Builder
    {
        return Post::query()
            ->select(['id', 'slug', 'title', 'title_translations', 'published_at'])
            ->when($term !== '', function (Builder $query) use ($term): void {
                $query->where(function (Builder $nested) use ($term): void {
                    $nested
                        ->where('slug', 'like', "%{$term}%")
                        ->orWhere('title', 'like', "%{$term}%")
                        ->orWhere('title_translations->en', 'like', "%{$term}%")
                        ->orWhere('title_translations->lt', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('published_at');
    }

    /**
     * @return Builder<Collection>
     */
    private static function collectionQuery(string $term): Builder
    {
        return Collection::query()
            ->select(['id', 'slug', 'name'])
            ->when($term !== '', function (Builder $query) use ($term): void {
                $query->where(function (Builder $nested) use ($term): void {
                    $nested
                        ->where('slug', 'like', "%{$term}%")
                        ->orWhere('name->en', 'like', "%{$term}%")
                        ->orWhere('name->lt', 'like', "%{$term}%");
                });
            })
            ->orderBy('name');
    }

    private static function buildPostResult(Post $post, string $locale): SearchResult
    {
        $title = self::resolvePostTitle($post, $locale);
        $slug = (string) ($post->getAttribute('slug') ?? '');
        $label = trim(sprintf('%s: %s', __('sliders.link_types.post'), $title !== '' ? $title : $slug));
        $url = route('frontend.posts.show', $post);

        return SearchResult::make($url, $label)
            ->withData('type', 'post');
    }

    private static function buildCollectionResult(Collection $collection, string $locale): SearchResult
    {
        $nameString = self::resolveCollectionName($collection->getAttribute('name'), $locale);
        $slug = (string) ($collection->getAttribute('slug') ?? '');
        $label = trim(sprintf('%s: %s', __('sliders.link_types.collection'), $nameString !== '' ? $nameString : $slug));
        $url = route('frontend.collections.show', $collection);

        return SearchResult::make($url, $label)
            ->withData('type', 'collection');
    }

    private static function resolvePostTitle(Post $post, string $locale): string
    {
        $translations = $post->getAttribute('title_translations');

        if (is_array($translations)) {
            $candidate = Arr::get($translations, $locale);
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }

            $fallback = Arr::first(array_filter($translations, fn ($value) => is_string($value) && $value !== ''));
            if (is_string($fallback)) {
                return $fallback;
            }
        }

        $rawTitle = $post->getAttribute('title');

        return is_string($rawTitle) ? $rawTitle : '';
    }

    private static function resolveCollectionName(mixed $nameAttribute, string $locale): string
    {
        if (is_array($nameAttribute)) {
            $candidate = Arr::get($nameAttribute, $locale);

            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }

            $fallback = Arr::first(array_filter($nameAttribute, fn ($value) => is_string($value) && $value !== ''));
            if (is_string($fallback)) {
                return $fallback;
            }
        }

        if (is_string($nameAttribute)) {
            return $nameAttribute;
        }

        return '';
    }
}
