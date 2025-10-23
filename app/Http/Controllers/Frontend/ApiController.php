<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ApiController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function searchProducts(Request $request): JsonResponse
    {
        $query = trim((string) $request->get('q', ''));

        // Keep the API predictable by clamping the requested limit to a safe range.
        $limit = (int) $request->integer('limit', 10);
        $limit = max(1, min($limit, 25));

        $products = Product::query()
            ->published()
            ->when($query !== '', static function ($productQuery) use ($query): void {
                // Apply a scoped LIKE search across both name and description columns.
                $productQuery->where(static function ($nestedQuery) use ($query): void {
                    $likeQuery = "%{$query}%";

                    $nestedQuery
                        ->where('name', 'like', $likeQuery)
                        ->orWhere('description', 'like', $likeQuery);
                });
            })
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'price'])
            ->map(static function (Product $product): array {
                // Normalize the payload so every consumer receives consistent media keys.
                return [
                    'id'    => $product->getKey(),
                    'name'  => $product->name,
                    'slug'  => $product->slug,
                    'price' => $product->price,
                    // Preserve the historical `image` field while introducing explicit media aliases.
                    'image'      => $product->main_image,
                    'main_image' => $product->main_image,
                    'thumbnail'  => $product->thumbnail,
                ];
            })
            ->values();

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
        $userId = $request->user()?->getAuthIdentifier();
        $sessionId = $request->session()->getId();

        $count = $this->cartService->getCount(
            $userId !== null ? (int) $userId : null,
            $sessionId
        );

        return response()->json(['count' => $count]);
    }

    public function getWishlistCount(Request $request): JsonResponse
    {
        $userId = $request->user()?->getAuthIdentifier();

        if ($userId === null) {
            return response()->json(['count' => 0]);
        }

        $count = WishlistItem::query()
            ->forUser((int) $userId)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function toggleWishlist(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $productId = (int) $request->integer('product_id');

        if ($productId <= 0 || ! Product::query()->whereKey($productId)->exists()) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $variantId = $request->has('variant_id') ? (int) $request->integer('variant_id') : null;

        $wishlist = $this->resolveDefaultWishlist((int) $user->getAuthIdentifier());

        $wishlistItemQuery = $wishlist->items()
            ->where('product_id', $productId)
            ->when($variantId, static fn ($query) => $query->where('variant_id', $variantId));

        $wishlistItem = $wishlistItemQuery->first();

        if ($wishlistItem !== null) {
            $wishlistItem->delete();
            $added = false;
        } else {
            $wishlist->items()->create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity'   => 1,
            ]);
            $added = true;
        }

        $count = $wishlist->items()->count();

        return response()->json([
            'added' => $added,
            'count' => $count,
        ]);
    }

    public function getRecentlyViewed(Request $request): JsonResponse
    {
        $recentlyViewed = array_values(array_unique(array_map('intval', (array) $request->session()->get('recently_viewed', []))));

        if ($recentlyViewed === []) {
            return response()->json([]);
        }

        // Preserve the visit order while trimming to the most recent entries only.
        $orderedIds = array_values(array_slice($recentlyViewed, 0, 10));

        $products = Product::query()
            ->published()
            ->whereIn('id', $orderedIds)
            ->get(['id', 'name', 'slug', 'price'])
            ->sortBy(static function (Product $product) use ($orderedIds): int {
                $position = array_search($product->getKey(), $orderedIds, true);

                return $position === false ? PHP_INT_MAX : $position;
            })
            ->values()
            ->map(static function (Product $product): array {
                // Mirror the search payload structure so widgets can reuse adapters.
                return [
                    'id'    => $product->getKey(),
                    'name'  => $product->name,
                    'slug'  => $product->slug,
                    'price' => $product->price,
                    // Maintain the legacy `image` attribute for downstream caches still expecting it.
                    'image'      => $product->main_image,
                    'main_image' => $product->main_image,
                    'thumbnail'  => $product->thumbnail,
                ];
            });

        return response()->json($products);
    }

    public function addRecentlyViewed(Request $request): JsonResponse
    {
        $productId = (int) $request->integer('product_id');

        if ($productId <= 0 || ! Product::query()->whereKey($productId)->exists()) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $recentlyViewed = array_values(array_filter(
            array_map('intval', (array) $request->session()->get('recently_viewed', [])),
            static fn (int $id) => $id !== $productId
        ));

        array_unshift($recentlyViewed, $productId);

        $recentlyViewed = array_slice($recentlyViewed, 0, 20);

        $request->session()->put('recently_viewed', $recentlyViewed);

        return response()->json(['success' => true]);
    }

    private function resolveDefaultWishlist(int $userId): UserWishlist
    {
        return UserWishlist::query()->firstOrCreate(
            ['user_id' => $userId, 'is_default' => true],
            ['name' => __('My Wishlist')]
        );
    }
}
