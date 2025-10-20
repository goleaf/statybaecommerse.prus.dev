<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Services\Shared\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): View
    {
        $featuredProducts = $this->productService->getFeaturedProducts(8);
        $latestProducts = $this->productService->getNewArrivals(8);

        $categoryTree = $this->buildCategoryTree();
        $featuredBrands = $this->loadFeaturedBrands();

        return view('home.index', [
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'categoryTree' => $categoryTree,
            'featuredBrands' => $featuredBrands,
        ]);
    }

    private function buildCategoryTree(): Collection
    {
        $locale = app()->getLocale();

        return Category::query()
            ->roots()
            ->ordered()
            ->with([
                'translations' => fn ($query) => $query->where('locale', $locale),
                'children' => function ($query) use ($locale) {
                    $query->ordered()
                        ->with(['translations' => fn ($childQuery) => $childQuery->where('locale', $locale), 'children']);
                },
            ])
            ->get();
    }

    private function loadFeaturedBrands(): Collection
    {
        return Brand::query()
            ->whereHas('products', fn ($query) => $query->published())
            ->withCount(['products as published_products_count' => fn ($query) => $query->published()])
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->limit(8)
            ->get();
    }
}
