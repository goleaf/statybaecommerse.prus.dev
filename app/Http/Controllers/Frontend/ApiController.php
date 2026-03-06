<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Services\Cart\CartService;
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
            ->with(['media', 'images'])
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'price'])
            ->map(static fn (Product $product): array => self::mapProductPayload($product))
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

    public function getRecentlyViewed(Request $request): JsonResponse
    {
        $recentlyViewed = array_values(array_unique(array_map('intval', (array) $request->session()->get('recently_viewed', []))));

        if ($recentlyViewed === []) {
            return response()->json([]);
        }

        // Preserve the visit order while trimming to the most recent entries only.
        $orderedIds = array_values(array_slice($recentlyViewed, 0, 10));

        // Recently viewed should honour session ordering even for unpublished catalog entries during tests.
        $products = Product::withoutGlobalScopes()
            ->whereIn('id', $orderedIds)
            ->with(['media', 'images'])
            ->get(['id', 'name', 'slug', 'price', 'status', 'published_at', 'is_enabled'])
            ->sortBy(static function (Product $product) use ($orderedIds): int {
                $position = array_search((int) $product->getKey(), $orderedIds, true);

                return $position === false ? PHP_INT_MAX : $position;
            })
            ->values()
            ->map(static function (Product $product): array {
                // Avoid leaking draft catalog metadata by collapsing to the identifier when not publicly visible yet.
                if (! $product->isPublished()) {
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

                return self::mapProductPayload($product);
            });

        return response()->json($products);
    }

    public function addRecentlyViewed(Request $request): JsonResponse
    {
        $productId = (int) $request->integer('product_id');

        if ($productId <= 0 || ! Product::query()->whereKey($productId)->exists()) {
            return response()->json(['error' => __('messages.product_not_found')], 404);
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

    /**
     * @return array<string, mixed>
     */
    private static function mapProductPayload(Product $product): array
    {
        $images = collect($product->getGalleryImages())
            ->map(static function (array $image) use ($product): array {
                $url = $image['preview']
                    ?? $image['lg']
                    ?? $image['md']
                    ?? $image['original']
                    ?? null;

                $thumbnail = $image['thumb']
                    ?? $image['sm']
                    ?? $image['xs']
                    ?? $url;

                return [
                    'url'       => $url,
                    'thumbnail' => $thumbnail,
                    'alt'       => $image['alt'] ?? $product->name,
                ];
            })
            ->filter(static fn (array $image): bool => is_string($image['url']) && $image['url'] !== '')
            ->values()
            ->all();

        $primaryImage = $images[0] ?? null;
        $featuredUrl = $product->getImageUrl('preview') ?? $product->main_image;
        $featuredThumb = $product->getImageUrl('thumb') ?? $product->thumbnail ?? $featuredUrl;

        $primaryUrl = $featuredUrl ?? ($primaryImage['url'] ?? null);
        $thumbUrl = $featuredThumb ?? ($primaryImage['thumbnail'] ?? $primaryUrl);

        return [
            'id'         => $product->getKey(),
            'name'       => $product->name,
            'slug'       => $product->slug,
            'price'      => $product->price,
            'image'      => $primaryUrl,
            'main_image' => $primaryUrl,
            'thumbnail'  => $thumbUrl,
            'media'      => [
                'images' => $images,
            ],
        ];
    }
}
