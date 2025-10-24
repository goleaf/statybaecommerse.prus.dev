<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Support\Frontend\DataProviders\BrandCatalogueDataProvider;
use App\Support\Frontend\DataProviders\ProductCatalogueDataProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BrandController extends Controller
{
    public function __construct(
        private readonly BrandCatalogueDataProvider $brandData,
        private readonly ProductCatalogueDataProvider $productData,
    ) {}

    public function index(Request $request): View
    {
        $data = $this->brandData->index($request->all());

        return view('frontend.brands.index', array_merge($data, [
            'highlightedBrands' => $this->productData->brandHighlights(12),
            'featuredProducts' => $this->productData->featured(4),
        ]));
    }

    public function show(Brand $brand, Request $request): View
    {
        return view('frontend.brands.show', $this->brandData->show($brand, $request->all()));
    }
}
