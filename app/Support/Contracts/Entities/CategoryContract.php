<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Models\Category;
use App\Support\Contracts\ContractPathResolver;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

final class CategoryContract
{
    public const CONTRACT = 'category';

    public const VERSION = 'v1';

    public static function schemaPath(): string
    {
        return ContractPathResolver::schema('category.schema.json');
    }

    public static function examplePath(): string
    {
        return ContractPathResolver::example('category.json');
    }

    public static function forCategory(Category $category, array $meta = []): array
    {
        $categoryPayload = self::mapCategory($category);

        return self::envelope([
            'category' => $categoryPayload,
            'item' => $categoryPayload,
        ], $meta);
    }

    public static function forCollection(iterable $categories, array $meta = []): array
    {
        $paginator = $categories instanceof LengthAwarePaginator ? $categories : null;
        $items = $paginator?->getCollection() ?? Collection::make($categories);
        $mapped = $items->map(fn (Category $category): array => self::mapCategory($category))->values()->all();

        $data = [
            'categories' => $mapped,
            'items' => $mapped,
        ];

        if ($paginator instanceof LengthAwarePaginator) {
            $data['pagination'] = [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ];
            $meta['total'] = $paginator->total();
        } else {
            $meta['total'] = count($mapped);
        }

        return self::envelope($data, $meta);
    }

    private static function mapCategory(Category $category): array
    {
        $category->loadMissing(['parent', 'children']);

        return [
            'id' => $category->getKey(),
            'slug' => (string) $category->slug,
            'name' => (string) $category->name,
            'description' => $category->description,
            'parent' => $category->parent?->exists ? [
                'id' => $category->parent->getKey(),
                'slug' => (string) $category->parent->slug,
                'name' => (string) $category->parent->name,
            ] : null,
            'children' => $category->children->map(fn (Category $child): array => [
                'id' => $child->getKey(),
                'slug' => (string) $child->slug,
                'name' => (string) $child->name,
                'description' => $child->description,
                'links' => [
                    'self' => self::categoryLink((string) $child->slug),
                ],
                'parent' => null,
                'children' => [],
                'product_count' => $child->products_count ?? null,
            ])->all(),
            'product_count' => $category->products_count ?? null,
            'links' => [
                'self' => self::categoryLink((string) $category->slug),
            ],
        ];
    }

    private static function categoryLink(string $slug): string
    {
        if (Route::has('categories.show')) {
            return route('categories.show', $slug);
        }

        return url('/categories/'.$slug);
    }

    private static function envelope(array $data, array $meta = []): array
    {
        $meta = array_merge([
            'generated_at' => now()->toISOString(),
        ], Arr::whereNotNull($meta));

        return [
            'contract' => self::CONTRACT,
            'version' => self::VERSION,
            'data' => $data,
            'meta' => $meta,
        ];
    }
}
