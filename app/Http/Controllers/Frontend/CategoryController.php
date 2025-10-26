<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
        $categories = Category::query()
            ->withCount(['products as published_products_count' => static fn (Builder $query) => $query->published()])
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('frontend.categories.index', [
            'categories'       => $categories,
            'topCategories'    => $this->productData->categoryHighlights(12),
            'featuredProducts' => $this->productData->featured(4),
        ]);
    }

    public function show(Category $category, Request $request): View
    {
        return view('frontend.categories.show', $this->categoryData->show($category, $request->all()));
    }
}
