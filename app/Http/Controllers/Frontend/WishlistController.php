<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use App\Http\Resources\WishlistResource;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $wishlistId = $request->integer('wishlist_id');
        $wishlist = $this->resolveWishlist($user, $wishlistId, createIfMissing: $wishlistId === null);

        if (! $wishlist instanceof UserWishlist) {
            abort($wishlistId !== null ? 403 : 404);
        }

        $perPage = max(1, (int) $request->integer('per_page', 12));

        $wishlistItems = $wishlist
            ->items()
            ->with(['product.media', 'product.brand', 'variant'])
            ->latest()
            ->paginate($perPage);

        if ($request->expectsJson()) {
            return new WishlistResource($wishlist, $wishlistItems);
        }

        return view('frontend.wishlist.index', [
            'wishlist' => $wishlist,
            'wishlistItems' => $wishlistItems,
        ]);
    }

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'wishlist_id' => ['nullable', 'integer'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $wishlist = $this->resolveWishlist($user, $data['wishlist_id'] ?? null);

        if (! $wishlist instanceof UserWishlist) {
            return $this->respond(
                $request,
                [
                    'status' => 'error',
                    'message' => __('You are not allowed to update this wishlist.'),
                ],
                __('You are not allowed to update this wishlist.'),
                403
            );
        }

        $variantId = $data['variant_id'] ?? null;

        if ($variantId !== null) {
            $variant = ProductVariant::query()->find($variantId);

            if ($variant === null || $variant->product_id !== (int) $data['product_id']) {
                return $this->respond(
                    $request,
                    [
                        'status' => 'error',
                        'message' => __('The selected variant does not belong to the given product.'),
                    ],
                    __('Unable to add the selected product to your wishlist.'),
                    422
                );
            }
        }

        $itemQuery = $wishlist
            ->items()
            ->where('product_id', $data['product_id']);

        if ($variantId === null) {
            $itemQuery->whereNull('variant_id');
        } else {
            $itemQuery->where('variant_id', $variantId);
        }

        $item = $itemQuery->first();

        $attributes = [
            'quantity' => $data['quantity'] ?? 1,
            'notes' => $data['notes'] ?? null,
        ];

        if ($item instanceof WishlistItem) {
            $item->fill($attributes);

            if ($item->isDirty()) {
                $item->save();
            }
        } else {
            $item = $wishlist->items()->create([
                'product_id' => $data['product_id'],
                'variant_id' => $variantId,
                ...$attributes,
            ]);
        }

        $item->load(['product', 'variant']);

        $payload = [
            'status' => 'added',
            'message' => __('Product added to your wishlist.'),
            'wishlist_count' => $wishlist->items()->count(),
            'item' => $this->transformItem($item),
        ];

        return $this->respond($request, $payload, __('Product added to your wishlist.'));
    }

    public function remove(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'wishlist_id' => ['nullable', 'integer'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $wishlist = $this->resolveWishlist($user, $data['wishlist_id'] ?? null, createIfMissing: false);

        if (! $wishlist instanceof UserWishlist) {
            return $this->respond(
                $request,
                [
                    'status' => 'error',
                    'message' => __('Your wishlist is already empty.'),
                ],
                __('Your wishlist is already empty.'),
                404
            );
        }

        $variantId = $data['variant_id'] ?? null;

        $itemQuery = $wishlist
            ->items()
            ->where('product_id', $data['product_id']);

        if ($variantId === null) {
            $itemQuery->whereNull('variant_id');
        } else {
            $itemQuery->where('variant_id', $variantId);
        }

        $item = $itemQuery->first();

        if (! $item instanceof WishlistItem) {
            return $this->respond(
                $request,
                [
                    'status' => 'error',
                    'message' => __('The requested item was not found in your wishlist.'),
                ],
                __('The requested item was not found in your wishlist.'),
                404
            );
        }

        $item->delete();

        $payload = [
            'status' => 'removed',
            'message' => __('Product removed from your wishlist.'),
            'wishlist_count' => $wishlist->items()->count(),
        ];

        return $this->respond($request, $payload, __('Product removed from your wishlist.'));
    }

    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $wishlistId = $request->integer('wishlist_id');
        $wishlist = $this->resolveWishlist($user, $wishlistId, createIfMissing: false);

        if (! $wishlist instanceof UserWishlist) {
            return $this->respond(
                $request,
                [
                    'status' => 'success',
                    'message' => __('Your wishlist is already empty.'),
                    'wishlist_count' => 0,
                ],
                __('Your wishlist is already empty.')
            );
        }

        $wishlist->items()->delete();

        $payload = [
            'status' => 'cleared',
            'message' => __('Your wishlist has been cleared.'),
            'wishlist_count' => 0,
        ];

        return $this->respond($request, $payload, __('Your wishlist has been cleared.'));
    }

    private function resolveWishlist(?User $user, ?int $wishlistId, bool $createIfMissing = true): ?UserWishlist
    {
        // Guard clause: deny access when no authenticated user is available.
        if (! $user instanceof User) {
            return null;
        }

        if ($wishlistId !== null) {
            return $user
                ->wishlists()
                ->whereKey($wishlistId)
                ->first();
        }

        return $this->resolveDefaultWishlist($user, $createIfMissing);
    }

    private function resolveDefaultWishlist(?User $user, bool $createIfMissing = true): ?UserWishlist
    {
        if (! $user instanceof User) {
            return null;
        }

        $wishlist = $user
            ->wishlists()
            ->where('is_default', true)
            ->first();

        if ($wishlist instanceof UserWishlist || ! $createIfMissing) {
            return $wishlist;
        }

        $user->wishlists()->where('is_default', true)->update(['is_default' => false]);

        return $user
            ->wishlists()
            ->create([
                'name' => __('My Wishlist'),
                'description' => __('Automatically created wishlist.'),
                'is_public' => false,
                'is_default' => true,
            ]);
    }

    private function respond(Request $request, array $payload, string $redirectMessage, int $status = 200): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json($payload, $status);
        }

        if ($status >= 400) {
            return redirect()
                ->route('frontend.wishlist.index')
                ->withErrors(['wishlist' => $redirectMessage]);
        }

        return redirect()
            ->route('frontend.wishlist.index')
            ->with('status', $redirectMessage);
    }

    private function transformItem(WishlistItem $item): array
    {
        $item->loadMissing(['product', 'variant']);

        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'variant_id' => $item->variant_id,
            'quantity' => $item->quantity,
            'notes' => $item->notes,
            'display_name' => $item->display_name,
            'current_price' => $item->current_price,
            'formatted_price' => $item->formatted_current_price,
        ];
    }
}
