<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

final class CollectionController extends Controller
{
    public function index(Request $request): View
    {
        $collections = Collection::query()
            ->visible()
            ->active()
            ->ordered()
            ->with(['media'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                if ($request->type === 'automatic') {
                    $query->automatic();
                } elseif ($request->type === 'manual') {
                    $query->manual();
                }
            })
            ->paginate(12);

        return view('collections.index', compact('collections'));
    }

    public function show(Collection $collection): View
    {
        $collection->load(['products' => function ($query) {
            $query->published()
                ->with(['media', 'variants', 'categories'])
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc');
        }]);

        $products = $collection->products()
            ->published()
            ->with(['media', 'variants', 'categories'])
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($collection->products_per_page ?? 12);

        // Get related collections
        $relatedCollections = Collection::query()
            ->visible()
            ->active()
            ->where('id', '!=', $collection->id)
            ->whereHas('products', function ($query) use ($collection) {
                $query->whereIn('id', $collection->products->pluck('id'));
            })
            ->limit(4)
            ->get();

        return view('collections.show', compact('collection', 'products', 'relatedCollections'));
    }

    public function products(Request $request, Collection $collection): JsonResponse
    {
        $products = $collection->products()
            ->published()
            ->with(['media', 'variants', 'categories'])
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            })
            ->when($request->filled('price_min'), function ($query) use ($request) {
                $query->where('price', '>=', $request->price_min);
            })
            ->when($request->filled('price_max'), function ($query) use ($request) {
                $query->where('price', '<=', $request->price_max);
            })
            ->when($request->filled('sort'), function ($query) use ($request) {
                match ($request->sort) {
                    'price_asc' => $query->orderBy('price', 'asc'),
                    'price_desc' => $query->orderBy('price', 'desc'),
                    'name_asc' => $query->orderBy('name', 'asc'),
                    'name_desc' => $query->orderBy('name', 'desc'),
                    'newest' => $query->orderBy('created_at', 'desc'),
                    'oldest' => $query->orderBy('created_at', 'asc'),
                    default => $query->orderBy('sort_order')->orderBy('created_at', 'desc'),
                };
            }, function ($query) {
                $query->orderBy('sort_order')->orderBy('created_at', 'desc');
            })
            ->paginate($collection->products_per_page ?? 12);

        return response()->json([
            'products' => $products,
            'collection' => $collection,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $collections = Collection::query()
            ->visible()
            ->active()
            ->where('name', 'like', '%' . $request->q . '%')
            ->orWhere('description', 'like', '%' . $request->q . '%')
            ->limit(10)
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json($collections);
    }
}
