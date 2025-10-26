<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\SearchQuerySanitizer;
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
        // Normalise the raw query and category identifier before constructing
        // any database constraints so we never interpolate untrusted input into
        // LIKE clauses or relation filters.
        $query = SearchQuerySanitizer::sanitize($request->get('q', ''));
        $categoryInput = $request->get('category');
        $selectedCategory = is_numeric($categoryInput) ? (int) $categoryInput : null;

        $products = collect();

        if ($query !== '') {
            $likePattern = SearchQuerySanitizer::toLikePattern($query);

            $products = Product::query()
                ->where('is_active', true)
                ->when($selectedCategory !== null, function ($builder) use ($selectedCategory) {
                    // Guard the category relationship filter by using the
                    // already sanitised integer so the ORM never receives raw
                    // request values.
                    return $builder->whereHas('categories', function ($q) use ($selectedCategory) {
                        $q->where('id', $selectedCategory);
                    });
                })
                ->where(function ($q) use ($likePattern) {
                    // Apply the escaped LIKE pattern across searchable
                    // columns so wildcard characters supplied by the shopper
                    // are treated as literals instead of SQL directives.
                    $q->where('name', 'like', $likePattern)
                        ->orWhere('description', 'like', $likePattern);
                })
                ->paginate(20)
                ->appends(array_filter([
                    'q'        => $query !== '' ? $query : null,
                    'category' => $selectedCategory,
                ], static fn ($value) => $value !== null && $value !== ''))
                ->withPath(route('frontend.search.index'));
        }

        $categories = Category::where('is_active', true)->get();

        return view('frontend.search.index', [
            'products'         => $products,
            'query'            => $query,
            'categories'       => $categories,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    /**
     * Get search suggestions.
     */
    public function suggestions(Request $request): JsonResponse
    {
        // Always trim and clean the query before performing partial matches so
        // API consumers cannot coerce the endpoint into leaking data via LIKE
        // wildcard abuse.
        $query = SearchQuerySanitizer::sanitize($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $likePattern = SearchQuerySanitizer::toLikePattern($query);

        $products = Product::where('is_active', true)
            ->where('name', 'like', $likePattern)
            ->limit(5)
            ->get(['id', 'name', 'slug'])
            ->map(function ($product) {
                return [
                    'id'   => $product->id,
                    'name' => $product->name,
                    'url'  => route('frontend.products.show', $product),
                ];
            });

        return response()->json($products);
    }

    /**
     * Get autocomplete suggestions.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        // Cleanse the query here as well so the autocomplete suggestions mirror
        // the behaviour of the results and suggestion endpoints.
        $query = SearchQuerySanitizer::sanitize($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = collect();

        // Product names
        $products = Product::where('is_active', true)
            ->where('name', 'like', SearchQuerySanitizer::toLikePattern($query))
            ->limit(3)
            ->pluck('name')
            ->map(function ($name) {
                return ['value' => $name, 'type' => 'product'];
            });

        $suggestions = $suggestions->merge($products);

        // Category names
        $categories = Category::where('is_active', true)
            ->where('name', 'like', SearchQuerySanitizer::toLikePattern($query))
            ->limit(2)
            ->pluck('name')
            ->map(function ($name) {
                return ['value' => $name, 'type' => 'category'];
            });

        $suggestions = $suggestions->merge($categories);

        return response()->json($suggestions->take(5));
    }
}
