<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = Str::of((string) $request->input('search'))->trim()->whenEmpty(fn () => null)->toString();

        $categoriesQuery = Category::query()
            ->withCount(['products as visible_products_count' => fn ($query) => $query->where('is_visible', true)])
            ->orderBy('name');

        if ($search) {
            $categoriesQuery->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            });
        }

        /** @var LengthAwarePaginator $categories */
        $categories = $categoriesQuery->paginate(12)->withQueryString();

        return view('frontend.categories.index', [
            'categories' => $categories,
            'search' => $search,
        ]);
    }

    public function show(Category $category, Request $request): View
    {
        $category->load(['children' => fn ($query) => $query->orderBy('name')]);

        $productsQuery = $category->products()
            ->with(['brand', 'media'])
            ->where('is_visible', true);

        $sort = $request->input('sort', 'latest');

        switch ($sort) {
            case 'price_asc':
                $productsQuery->orderBy('price');
                break;
            case 'price_desc':
                $productsQuery->orderByDesc('price');
                break;
            case 'name_asc':
                $productsQuery->orderBy('name');
                break;
            case 'name_desc':
                $productsQuery->orderByDesc('name');
                break;
            default:
                $productsQuery->latest();
        }

        /** @var LengthAwarePaginator $products */
        $products = $productsQuery->paginate(12)->withQueryString();

        return view('frontend.categories.show', [
            'category' => $category,
            'products' => $products,
            'sort' => $sort,
        ]);
    }
}
