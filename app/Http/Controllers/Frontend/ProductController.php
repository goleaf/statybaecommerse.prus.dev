<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\Frontend\DataProviders\BrandCatalogueDataProvider;
use App\Support\Frontend\DataProviders\CategoryCatalogueDataProvider;
use App\Support\Frontend\DataProviders\ProductCatalogueDataProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ProductController extends Controller
{
    public function __construct(private readonly ProductCatalogueDataProvider $dataProvider) {}

    public function index(Request $request): View
    {
        $data = $this->dataProvider->getListingData($request->all());

        return view('frontend.products.index', $data);
    }

    public function search(Request $request): View
    {
        $data = $this->dataProvider->getListingData($request->all());

        return view('frontend.products.index', $data);
    }

    public function byCategory(Category $category, Request $request, CategoryCatalogueDataProvider $categories): View
    {
        $data = $categories->show($category, $request->all());

        return view('frontend.categories.show', $data);
    }

    public function byBrand(Brand $brand, Request $request, BrandCatalogueDataProvider $brands): View
    {
        $data = $brands->show($brand, $request->all());

        return view('frontend.brands.show', $data);
    }

    public function show(Product $product): View
    {
        $data = $this->dataProvider->getProductDetailData($product);

        return view('frontend.products.show', $data);
    }

    public function addReview(Product $product): RedirectResponse
    {
        return redirect()->route('frontend.products.show', $product);
    }
}
