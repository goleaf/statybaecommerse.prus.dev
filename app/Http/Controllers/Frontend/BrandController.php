<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\Frontend\BrandShowcaseDataProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class BrandController extends Controller
{
    public function __construct(private readonly BrandShowcaseDataProvider $dataProvider)
    {
    }

    public function index(Request $request): View
    {
        return view('frontend.brands.index', [
            'brands' => $this->dataProvider->indexBrands(),
        ]);
    }

    public function show(Request $request, Brand $brand): View
    {
        $brand = $this->dataProvider->loadBrand($brand);
        $filters = $this->dataProvider->resolveFilters($request);
        $products = $this->dataProvider->products($brand, $filters, 12)->withQueryString();

        return view('frontend.brands.show', [
            'brand' => $brand,
            'breadcrumbs' => $this->dataProvider->breadcrumbs($brand),
            'products' => $products,
            'availableSorts' => $this->dataProvider->availableSorts(),
            'activeFilters' => $filters,
        ]);
    }
}
