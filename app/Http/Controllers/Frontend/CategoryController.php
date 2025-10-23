<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

final class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('frontend.categories.index', compact('categories'));
    }

    public function show(Category $category): View
    {
        $category->load(['children']);

        $products = Product::query()
            ->with(['brand'])
            ->whereHas('categories', fn ($query) => $query->whereKey($category->getKey()))
            ->paginate(12)
            ->withQueryString();

        return view('frontend.categories.show', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
