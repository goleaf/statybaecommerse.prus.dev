<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders;

use App\Models\Product;
use App\Support\Frontend\DataProviders\Concerns\BuildsProductCatalogueQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ProductCatalogueDataProvider
{
    use BuildsProductCatalogueQuery;

    /**
     * Retrieve a paginated catalogue view.
     *
     * @param  callable(Builder):Builder|callable(Builder):void|null  $scoped
     */
    public function listing(array $filters = [], ?callable $scoped = null): array
    {
        $query = $this->baseProductQuery();

        if ($scoped !== null) {
            $scoped($query);
        }

        $applied = $this->applyCatalogueConstraints($query, $filters);
        $perPage = $this->resolvePerPage($filters);

        /** @var LengthAwarePaginator $products */
        $products = $query
            ->paginate($perPage)
            ->withQueryString();

        return [
            'products' => $products,
            'appliedFilter' => $applied['filter'],
            'appliedSort' => $applied['sort'],
            'searchTerm' => $applied['keyword'],
            'perPage' => $perPage,
            'availableFilters' => [
                '' => __('All products'),
                'featured' => __('Featured'),
                'sale' => __('On sale'),
            ],
            'availableSorts' => [
                'latest' => __('Newest first'),
                'price_asc' => __('Price: Low to High'),
                'price_desc' => __('Price: High to Low'),
                'name' => __('Name A-Z'),
            ],
        ];
    }

    public function detail(Product $product): array
    {
        $product->loadMissing([
            'brand:id,name,slug,description,website',
            'categories:id,name,slug,parent_id',
            'media',
        ]);

        $relatedQuery = $this->baseProductQuery()->whereKeyNot($product->getKey());

        $categoryIds = $product->categories->pluck('id')->filter();
        if ($categoryIds->isNotEmpty()) {
            $relatedQuery->whereHas('categories', static function (Builder $builder) use ($categoryIds): void {
                $builder->whereIn('categories.id', $categoryIds);
            });
        } elseif ($product->brand_id) {
            $relatedQuery->where('brand_id', $product->brand_id);
        }

        /** @var Collection<int, Product> $related */
        $related = $relatedQuery
            ->limit(8)
            ->get();

        return [
            'product' => $product,
            'relatedProducts' => $related,
        ];
    }
}
