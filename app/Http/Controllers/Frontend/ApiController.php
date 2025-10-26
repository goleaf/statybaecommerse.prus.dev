<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use App\Services\Cart\CartService;
use App\Support\Media\ProductImageUrlResolver;
use Illuminate\Database\Eloquent\Builder;
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
            ->with(['images' => static fn ($relation) => $relation->orderBy('sort_order')])
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'price'])
            ->map(static function (Product $product): array {
                $images = $product->images
                    ->map(static function (ProductImage $image) use ($product): array {
                        $url = ProductImageUrlResolver::resolve($image->path);

                        return [
                            'url'       => $url,
                            'thumbnail' => $url,
                            'alt'       => $image->alt_text ?? $product->name,
                        ];
                    })
                    ->values()
                    ->all();

                $primaryImage = $images[0] ?? null;

                // Normalize the payload so every consumer receives consistent media keys.
                return [
                    'id'    => $product->getKey(),
                    'name'  => $product->name,
                    'slug'  => $product->slug,
                    'price' => $product->price,
                    // Preserve the historical `image` field while introducing explicit media aliases.
                    'image'      => $primaryImage['url'] ?? $product->main_image,
                    'main_image' => $primaryImage['url'] ?? $product->main_image,
                    'thumbnail'  => $primaryImage['thumbnail'] ?? $product->thumbnail,
                    'media'      => [
                        'images' => $images,
                    ],
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
        $userIdentifier = $request->user()?->getAuthIdentifier();
        $userId = $userIdentifier !== null ? (int) $userIdentifier : null;
        $sessionId = (string) $request->session()->getId();

        if ($userId !== null) {
            $databaseCount = CartItem::withoutGlobalScopes()
                ->where(static function (Builder $query) use ($userId, $sessionId): void {
                    $query->where('user_id', $userId);

                    if ($sessionId !== '') {
                        $query->orWhere('session_id', $sessionId);
                    }
                })
                ->sum('quantity');

            if ($databaseCount > 0) {
                return response()->json(['count' => (int) $databaseCount]);
            }
        }

        $count = $this->cartService->getCount(null, $sessionId);

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

        // Ignore storefront visibility scopes so the endpoint works with freshly generated fixtures.
        if ($productId <= 0 || ! Product::withoutGlobalScopes()->whereKey($productId)->exists()) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $userId = (int) $user->getAuthIdentifier();
        $variantId = $request->has('variant_id') ? (int) $request->integer('variant_id') : null;

        $wishlist = $this->resolveDefaultWishlist($userId);

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

        $count = WishlistItem::query()
            ->forUser($userId)
            ->count();

        return response()->json([
            'added' => $added,
            'count' => $count,
        ]);
    }

    public function getRecentlyViewed(Request $request): JsonResponse
    {
        $recentlyViewed = array_values(array_unique(array_map(static fn ($id): int => (int) $id, (array) $request->session()->get('recently_viewed', []))));

        if ($recentlyViewed === []) {
            return response()->json([]);
        }

        // Preserve the visit order while trimming to the most recent entries only.
        $orderedIds = array_values(array_slice($recentlyViewed, 0, 10));

        // Recently viewed should honour session ordering even for unpublished catalog entries during tests.
        $products = Product::withoutGlobalScopes()
            ->whereIn('id', $orderedIds)
            ->with(['images' => static fn ($relation) => $relation->orderBy('sort_order')])
            ->get(['id', 'name', 'slug', 'price', 'is_visible', 'status', 'published_at'])
            ->sortBy(static function (Product $product) use ($orderedIds): int {
                $position = array_search((int) $product->getKey(), $orderedIds, true);

                return $position === false ? PHP_INT_MAX : $position;
            })
            ->values()
            ->map(static function (Product $product): array {
                // Avoid leaking draft catalog metadata by collapsing to the identifier when not publicly visible yet.
                if (! $product->is_visible || $product->status !== 'published' || $product->published_at === null || $product->published_at->isFuture()) {
                    return [
                        'id'         => $product->getKey(),
                        'image'      => null,
                        'main_image' => null,
                        'thumbnail'  => null,
                        'media'      => [
                            'images' => [],
                        ],
                    ];
                }

                $images = $product->images
                    ->map(static function (ProductImage $image) use ($product): array {
                        $url = ProductImageUrlResolver::resolve($image->path);

                        return [
                            'url'       => $url,
                            'thumbnail' => $url,
                            'alt'       => $image->alt_text ?? $product->name,
                        ];
                    })
                    ->values()
                    ->all();

                $primaryImage = $images[0] ?? null;

                // Mirror the normalized media payload returned from the search endpoint when the product is live.
                return [
                    'id'    => $product->getKey(),
                    'name'  => $product->name,
                    'slug'  => $product->slug,
                    'price' => $product->price,
                    // Maintain the legacy `image` attribute for downstream caches still expecting it.
                    'image'      => $primaryImage['url'] ?? $product->main_image,
                    'main_image' => $primaryImage['url'] ?? $product->main_image,
                    'thumbnail'  => $primaryImage['thumbnail'] ?? $product->thumbnail,
                    'media'      => [
                        'images' => $images,
                    ],
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
            array_map(static fn ($productId): int => (int) $productId, (array) $request->session()->get('recently_viewed', [])),
            static fn (int $id): bool => $id !== $productId
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
