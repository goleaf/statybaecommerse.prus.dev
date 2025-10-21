<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\Frontend\DataProviders\ProductCatalogueDataProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ProductController extends Controller
{
    public function __construct(private readonly ProductCatalogueDataProvider $dataProvider) {}

    public function index(Request $request): View
    {
        $listing = $this->dataProvider->listing($request->all());

        return view('frontend.products.index', $listing);
    }

    public function search(Request $request): View
    {
        $filters = array_merge($request->all(), [
            'q' => $request->input('q', $request->input('search', '')),
        ]);

        $listing = $this->dataProvider->listing($filters);

        return view('frontend.products.index', array_merge($listing, [
            'isSearch' => true,
        ]));
    }

    public function show(Product $product): View
    {
        $data = $this->dataProvider->detail($product);

        return view('frontend.products.show', $data);
    }

    public function byCategory(Category $category): RedirectResponse
    {
        return redirect()->route('frontend.categories.show', $category);
    }

    public function byBrand(Brand $brand): RedirectResponse
    {
        return redirect()->route('frontend.brands.show', $brand);
    }

    public function addReview(Product $product): RedirectResponse
    {
        return redirect()->route('frontend.products.show', $product);
    }
}
