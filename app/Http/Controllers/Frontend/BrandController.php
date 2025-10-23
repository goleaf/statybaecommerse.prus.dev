<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Support\Frontend\DataProviders\BrandCatalogueDataProvider;
use App\Support\Frontend\DataProviders\ProductCatalogueDataProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class BrandController extends Controller
{
    public function __construct(private readonly BrandCatalogueDataProvider $dataProvider) {}

    public function index(Request $request): View
    {
        $brands = Brand::query()
            ->where('is_visible', true)
            ->withCount(['products as published_products_count' => static fn (Builder $query) => $query->published()])
            ->orderByDesc('published_products_count')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('frontend.brands.index', [
            'brands' => $brands,
            'highlightedBrands' => $this->productData->brandHighlights(12),
            'featuredProducts' => $this->productData->featured(4),
        ]);
    }

    public function show(Brand $brand, Request $request): View
    {
        return view('frontend.brands.show', $this->brandData->show($brand, $request->all()));
    }
}
