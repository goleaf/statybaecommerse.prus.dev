<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders;

use App\Models\Category;
use App\Support\Frontend\DataProviders\Concerns\BuildsProductCatalogueQuery;
use Illuminate\Database\Eloquent\Builder;

final class CategoryCatalogueDataProvider
{
    use BuildsProductCatalogueQuery;

    public function __construct(private readonly ProductCatalogueDataProvider $products)
    {
    }

    public function index(): array
    {
        $categories = Category::query()
            ->with(['children' => static function (Builder $query): void {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return [
            'categories' => $categories,
        ];
    }

    public function show(Category $category, array $filters = []): array
    {
        $category->loadMissing([
            'parent:id,name,slug',
            'children' => static function (Builder $query): void {
                $query->orderBy('name');
            },
        ]);

        $listing = $this->products->listing(
            $filters,
            static function (Builder $query) use ($category): void {
                $query->whereHas('categories', static function (Builder $builder) use ($category): void {
                    $builder->where('categories.id', $category->getKey());
                });
            }
        );

        return array_merge($listing, [
            'category' => $category,
        ]);
    }
}
