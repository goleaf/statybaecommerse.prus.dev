<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
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

        return view('frontend.categories.show', array_merge($data, [
            'currentCategory' => $category,
        ]));
    }

    public function byBrand(Brand $brand, Request $request, BrandCatalogueDataProvider $brands): View
    {
        $data = $brands->show($brand, $request->all());

        return view('frontend.brands.show', array_merge($data, [
            'currentBrand' => $brand,
        ]));
    }

    public function show(Product $product): View
    {
        $data = $this->dataProvider->getProductDetailData($product);

        return view('frontend.products.show', $data);
    }

    public function addReview(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'title'   => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $user = $request->user();

        Review::create([
            'product_id'     => $product->getKey(),
            'user_id'        => $user?->getKey(),
            'rating'         => (int) $validated['rating'],
            'title'          => $validated['title'] ?? null,
            'content'        => $validated['content'],
            'reviewer_name'  => $user?->name,
            'reviewer_email' => $user?->email,
            'locale'         => app()->getLocale(),
            'is_approved'    => false,
        ]);

        return redirect()->route('frontend.products.show', $product);
    }
}
