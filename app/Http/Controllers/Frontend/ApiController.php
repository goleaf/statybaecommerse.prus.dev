<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ApiController extends Controller
{
    public function searchProducts(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'price', 'image']);

        return response()->json($products);
    }

    public function getCategoryTree(): JsonResponse
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->get();

        return response()->json($categories);
    }

    public function getCartCount(Request $request): JsonResponse
    {
        $count = 0;

        if (auth()->check()) {
            $count = Cart::where('user_id', auth()->id())->sum('quantity');
        } else {
            $count = session('cart', []);
            $count = is_array($count) ? count($count) : 0;
        }

        return response()->json(['count' => $count]);
    }

    public function getWishlistCount(Request $request): JsonResponse
    {
        $count = 0;

        if (auth()->check()) {
            $count = Wishlist::where('user_id', auth()->id())->count();
        }

        return response()->json(['count' => $count]);
    }

    public function toggleWishlist(Request $request): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $productId = $request->get('product_id');

        $wishlist = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $added = false;
        } else {
            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $productId,
            ]);
            $added = true;
        }

        return response()->json(['added' => $added]);
    }

    public function getRecentlyViewed(Request $request): JsonResponse
    {
        $recentlyViewed = session('recently_viewed', []);

        $products = Product::whereIn('id', $recentlyViewed)
            ->limit(10)
            ->get(['id', 'name', 'price', 'image']);

        return response()->json($products);
    }

    public function addRecentlyViewed(Request $request): JsonResponse
    {
        $productId = $request->get('product_id');

        $recentlyViewed = session('recently_viewed', []);

        // Remove if already exists
        $recentlyViewed = array_filter($recentlyViewed, fn ($id) => $id != $productId);

        // Add to beginning
        array_unshift($recentlyViewed, $productId);

        // Keep only last 20
        $recentlyViewed = array_slice($recentlyViewed, 0, 20);

        session(['recently_viewed' => $recentlyViewed]);

        return response()->json(['success' => true]);
    }
}
