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
use Illuminate\Support\Facades\Route;

final class ProductController extends Controller
{
    public function __construct(private readonly ProductCatalogueDataProvider $dataProvider) {}

    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectLegacyCategoryIdentifier($request)) {
            return $redirect;
        }

        $data = $this->dataProvider->getListingData($request->all());

        return view('frontend.products.index', $data);
    }

    public function search(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectLegacyCategoryIdentifier($request)) {
            return $redirect;
        }

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

    public function show(Product $product): RedirectResponse|View
    {
        $locale = app()->getLocale();
        if (! is_string($locale) || $locale === '') {
            $locale = config('app.locale', 'lt');
        }

        $productSlug = $product->slug ?? (string) $product->getRouteKey();

        if (method_exists($product, 'trans')) {
            $translatedSlug = $product->trans('slug');
            if (is_string($translatedSlug) && $translatedSlug !== '') {
                $productSlug = $translatedSlug;
            }
        }

        if (Route::has('frontend.products.show') && ! request()->routeIs('frontend.products.show')) {
            return redirect()->route('frontend.products.show', [
                'locale'  => $locale,
                'product' => $productSlug,
            ]);
        }

        $product->loadMissing(['brand', 'categories']);

        return view('frontend.products.show', [
            'product'         => $product,
            'primaryCategory' => $product->categories->first(),
            'relatedProducts' => $product->getRelatedProducts(4),
        ]);
    }

    private function redirectLegacyCategoryIdentifier(Request $request): ?RedirectResponse
    {
        $categoryQuery = $request->query('category');
        if (! is_scalar($categoryQuery)) {
            return null;
        }

        $categoryValue = trim((string) $categoryQuery);
        if ($categoryValue === '' || ! ctype_digit($categoryValue)) {
            return null;
        }

        $category = Category::query()->find((int) $categoryValue);
        if (! $category) {
            return null;
        }

        $categorySlug = $category->getTranslatedSlug() ?: $category->slug;
        if (! is_string($categorySlug) || $categorySlug === '') {
            return null;
        }

        return redirect()->to($request->fullUrlWithQuery(['category' => $categorySlug]), 301);
    }
}

