<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Support\Frontend\DataProviders\CategoryCatalogueDataProvider;
use App\Support\Frontend\DataProviders\ProductCatalogueDataProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryCatalogueDataProvider $categoryData,
        private readonly ProductCatalogueDataProvider $productData,
    ) {}

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $brandSlug = $request->string('brand')->trim()->toString();
        $collectionSlug = $request->string('collection')->trim()->toString();

        $categories = Category::query()
            ->withCount(['products as published_products_count' => static fn (Builder $query) => $query->published()])
            ->when($search !== '', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
            ->when($brandSlug !== '', fn (Builder $q) => $q->whereHas('products', function (Builder $inner) use ($brandSlug): void {
                $inner->published()->whereHas('brand', fn (Builder $b) => $b->where('slug', $brandSlug));
            }))
            ->when($collectionSlug !== '', fn (Builder $q) => $q->whereHas('products', function (Builder $inner) use ($collectionSlug): void {
                $inner->published()->whereHas('collections', fn (Builder $b) => $b->where('slug', $collectionSlug));
            }))
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $brands = Brand::query()
            ->select(['id', 'name', 'slug'])
            ->where('is_visible', true)
            ->whereHas('products', fn (Builder $q) => $q->published())
            ->orderBy('name')
            ->get();

        $collections = Collection::query()
            ->select(['id', 'name', 'slug'])
            ->where('is_visible', true)
            ->whereHas('products', fn (Builder $q) => $q->published())
            ->orderBy('name')
            ->get();

        return view('frontend.categories.index', [
            'categories'       => $categories,
            'brands'           => $brands,
            'collections'      => $collections,
            'topCategories'    => $this->productData->categoryHighlights(12),
            'featuredProducts' => $this->productData->featured(4),
            'activeSearch'     => $search,
            'activeBrand'      => $brandSlug,
            'activeCollection' => $collectionSlug,
        ]);
    }

    public function show(Category $category, Request $request): View
    {
        return view('frontend.categories.show', $this->categoryData->show($category, $request->all()));
    }
}
