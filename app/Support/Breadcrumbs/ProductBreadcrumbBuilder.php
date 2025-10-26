<?php

declare(strict_types=1);

namespace App\Support\Breadcrumbs;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

final class ProductBreadcrumbBuilder
{
    /**
     * Build breadcrumb items for rendering within the UI.
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    public static function display(Product $product): array
    {
        return self::baseItems($product)->all();
    }

    /**
     * Build breadcrumb items formatted for Schema.org `BreadcrumbList` JSON-LD.
     *
     * @return array<int, array{'@type': string, position: int, name: string, item: string|null}>
     */
    public static function schema(Product $product): array
    {
        return self::schemaFromDisplay(self::display($product));
    }

    /**
     * Convert UI breadcrumb items into Schema.org list items.
     *
     * @param  array<int, array{label: string, url: string|null}>                                 $displayItems
     * @return array<int, array{'@type': string, position: int, name: string, item: string|null}>
     */
    public static function schemaFromDisplay(array $displayItems): array
    {
        return collect($displayItems)
            ->values()
            ->map(function (array $item, int $index) {
                return [
                    '@type'    => 'ListItem',
                    'position' => $index + 1,
                    'name'     => $item['label'],
                    'item'     => $item['url'],
                ];
            })
            ->all();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{label: string, url: string|null}>
     */
    private static function baseItems(Product $product): Collection
    {
        $items = collect();

        $items->push([
            'label' => __('breadcrumbs.department'),
            'url'   => url('/'),
        ]);

        if ($category = self::resolvePrimaryCategory($product)) {
            $items->push([
                'label' => $category->trans('name') ?? $category->name,
                'url'   => self::resolveCategoryUrl($category),
            ]);
        }

        $items->push([
            'label' => $product->trans('name') ?? $product->name,
            'url'   => self::resolveProductUrl($product),
        ]);

        return $items
            ->filter(fn (array $item) => filled($item['label']))
            ->values();
    }

    private static function resolveProductUrl(Product $product): ?string
    {
        $slug = $product->trans('slug') ?? $product->slug;

        return $slug ? route('product.show', $slug) : null;
    }

    private static function resolvePrimaryCategory(Product $product): ?Category
    {
        if ($product->relationLoaded('categories')) {
            return $product->categories->first();
        }

        if (method_exists($product, 'categories')) {
            return $product->categories()->first();
        }

        return null;
    }

    private static function resolveCategoryUrl(Category $category): ?string
    {
        $slug = method_exists($category, 'trans')
            ? ($category->trans('slug') ?? $category->slug ?? null)
            : ($category->slug ?? null);

        if (! $slug) {
            return null;
        }

        return route('localized.categories.show', [
            'locale'   => app()->getLocale(),
            'category' => $slug,
        ]);
    }
}
