<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\SearchQuerySanitizer;
use Illuminate\Database\Eloquent\Builder;
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
                ->enabled()
                ->published()
                ->when($selectedCategory !== null, function (Builder $builder) use ($selectedCategory): Builder {
                    // Guard the category relationship filter by using the
                    // already sanitised integer so the ORM never receives raw
                    // request values.
                    return $builder->whereHas('categories', static function (Builder $q) use ($selectedCategory): void {
                        $q->where('categories.id', $selectedCategory);
                    });
                })
                ->where(static function (Builder $q) use ($likePattern): void {
                    // Apply the escaped LIKE pattern across searchable
                    // columns so wildcard characters supplied by the shopper
                    // are treated as literals instead of SQL directives.
                    $q->where('name', 'like', $likePattern)
                        ->orWhere('description', 'like', $likePattern)
                        ->orWhere('sku', 'like', $likePattern);
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

        $products = Product::query()
            ->enabled()
            ->published()
            ->where(static function (Builder $builder) use ($likePattern): void {
                $builder->where('name', 'like', $likePattern)
                    ->orWhere('sku', 'like', $likePattern);
            })
            ->limit(5)
            ->get(['id', 'name', 'slug'])
            ->map(static function ($product): array {
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
        $products = Product::query()
            ->enabled()
            ->published()
            ->where(static function (Builder $builder) use ($query): void {
                $likePattern = SearchQuerySanitizer::toLikePattern($query);
                $builder->where('name', 'like', $likePattern)
                    ->orWhere('sku', 'like', $likePattern);
            })
            ->limit(3)
            ->pluck('name')
            ->map(static function ($name): array {
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
