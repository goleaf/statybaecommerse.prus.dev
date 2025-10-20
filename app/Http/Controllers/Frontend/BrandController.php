<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\View\View;

final class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::query()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('frontend.brands.index', compact('brands'));
    }

    public function show(Brand $brand): View
    {
        $brand->load('products');

        $products = Product::query()
            ->where('brand_id', $brand->getKey())
            ->with(['brand', 'categories'])
            ->paginate(12)
            ->withQueryString();

        return view('frontend.brands.show', [
            'brand' => $brand,
            'products' => $products,
        ]);
    }
}
