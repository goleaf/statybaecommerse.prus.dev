<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Frontend\CategoryPageDataProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class CategoryController extends Controller
{
    public function __construct(private readonly CategoryPageDataProvider $dataProvider)
    {
    }

    public function index(Request $request): View
    {
        return view('frontend.categories.index', [
            'categories' => $this->dataProvider->indexCategories(),
        ]);
    }

    public function show(Request $request, Category $category): View
    {
        $category = $this->dataProvider->loadCategory($category);
        $filters = $this->dataProvider->resolveFilters($request);
        $products = $this->dataProvider->products($category, $filters, 12)->withQueryString();

        return view('frontend.categories.show', [
            'category' => $category,
            'breadcrumbs' => $this->dataProvider->breadcrumbs($category),
            'childCategories' => $this->dataProvider->childCategories($category),
            'products' => $products,
            'availableSorts' => $this->dataProvider->availableSorts(),
            'activeFilters' => $filters,
            'brands' => $this->dataProvider->brands(),
        ]);
    }
}
