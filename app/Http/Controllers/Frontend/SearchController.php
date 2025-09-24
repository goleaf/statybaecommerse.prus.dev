<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SearchController extends Controller
{
    /**
     * Display search results.
     */
    public function index(Request $request): View
    {
        $query = $request->get('q', '');
        $category = $request->get('category');

        $products = collect();

        if (! empty($query)) {
            $products = Product::query()
                ->where('is_active', true)
                ->when($category, function ($query, $category) {
                    return $query->whereHas('categories', function ($q) use ($category) {
                        $q->where('id', $category);
                    });
                })
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->paginate(20);
        }

        $categories = Category::where('is_active', true)->get();

        return view('frontend.search.index', compact('products', 'query', 'categories'));
    }

    /**
     * Get search suggestions.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name', 'slug'])
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'url' => route('products.show', $product->slug),
                ];
            });

        return response()->json($products);
    }

    /**
     * Get autocomplete suggestions.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = collect();

        // Product names
        $products = Product::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->limit(3)
            ->pluck('name')
            ->map(function ($name) {
                return ['value' => $name, 'type' => 'product'];
            });

        $suggestions = $suggestions->merge($products);

        // Category names
        $categories = Category::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->limit(2)
            ->pluck('name')
            ->map(function ($name) {
                return ['value' => $name, 'type' => 'category'];
            });

        $suggestions = $suggestions->merge($categories);

        return response()->json($suggestions->take(5));
    }
}
