<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Contracts\View\View;

use function now;

final class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::query()
            ->withoutGlobalScopes()
            ->where('is_enabled', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description']);

        return view('frontend.brands.index', [
            'brands' => $brands,
        ]);
    }

    public function show(Brand $brand): View
    {
        $brand->load(['media', 'translations']);

        $products = $brand->products()
            ->withoutGlobalScopes()
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['media', 'prices.currency', 'categories'])
            ->paginate(12);

        return view('frontend.brands.show', [
            'brand' => $brand,
            'products' => $products,
        ]);
    }
}
