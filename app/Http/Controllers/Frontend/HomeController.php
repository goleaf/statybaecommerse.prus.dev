<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function index(): View
    {
        $featuredProducts = Product::query()
            ->where('is_featured', true)
            ->latest('published_at')
            ->limit(8)
            ->with(['brand'])
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::query()
                ->latest('published_at')
                ->limit(8)
                ->with(['brand'])
                ->get();
        }

        $latestProducts = Product::query()
            ->latest('created_at')
            ->limit(8)
            ->with(['brand'])
            ->get();

        $popularCategories = Category::query()
            ->withCount('products')
            ->orderByDesc('products_count')
            ->limit(6)
            ->get();

        $popularBrands = Brand::query()
            ->withCount('products')
            ->orderByDesc('products_count')
            ->limit(6)
            ->get();

        $activeDiscounts = Discount::query()
            ->active()
            ->orderByDesc('priority')
            ->limit(4)
            ->get();

        return view('frontend.home.index', [
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'popularCategories' => $popularCategories,
            'popularBrands' => $popularBrands,
            'activeDiscounts' => $activeDiscounts,
        ]);
    }
}
