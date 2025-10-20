<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Frontend\ProductListingDataProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ProductController extends Controller
{
    public function __construct(private readonly ProductListingDataProvider $dataProvider)
    {
    }

    public function index(Request $request): View
    {
        $filters = $this->dataProvider->resolveFilters($request);
        $products = $this->dataProvider->paginatedProducts($filters, 12)->withQueryString();

        return view('frontend.products.index', [
            'products' => $products,
            'categories' => $this->dataProvider->categories(),
            'brands' => $this->dataProvider->brands(),
            'availableSorts' => $this->dataProvider->availableSorts(),
            'activeFilters' => $filters,
        ]);
    }

    public function show(Product $product): View
    {
        if (! $product->isPublished()) {
            abort(404);
        }

        $product = $this->dataProvider->loadProduct($product);

        return view('frontend.products.show', [
            'product' => $product,
            'relatedProducts' => $this->dataProvider->relatedProducts($product),
        ]);
    }
}
