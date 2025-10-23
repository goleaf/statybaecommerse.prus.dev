<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;

use function now;

final class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withoutGlobalScopes()
            ->where('is_visible', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description']);

        return view('frontend.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function show(Category $category): View
    {
        $category->load(['media', 'translations', 'children']);

        $products = $category->products()
            ->withoutGlobalScopes()
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['media', 'prices.currency', 'brand'])
            ->paginate(12);

        return view('frontend.categories.show', [
            'category' => $category,
            'childCategories' => $category->children,
            'products' => $products,
        ]);
    }
}
