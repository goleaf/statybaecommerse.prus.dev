<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Models\Category;
use function array_filter;
use function array_map;
use function explode;
use function trim;

final class CategoryContract
{
    public static function fromModel(Category $category): array
    {
        $category->loadMissing('children');

        return [
            'id' => (int) $category->getKey(),
            'slug' => (string) $category->slug,
            'title' => (string) ($category->name ?? ''),
            'parent_id' => $category->parent_id ? (int) $category->parent_id : null,
            'path' => self::buildPath($category),
            'order' => (int) ($category->sort_order ?? 0),
            'children' => $category->children->map(static fn (Category $child): array => self::fromModel($child))->values()->toArray(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function buildPath(Category $category): array
    {
        $fullPath = (string) ($category->full_path ?? $category->slug ?? '');
        $segments = array_map(static fn (string $value): string => trim($value), explode('/', $fullPath));

        return array_values(array_filter($segments, static fn (string $value): bool => $value !== ''));
    }
}
