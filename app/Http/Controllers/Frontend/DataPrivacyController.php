<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\DeleteAccountRequest;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DataPrivacyController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        /** @var Collection<int, Address> $addresses */
        $addresses = $user->addresses()
            ->orderByDesc('created_at')
            ->get([
                'id',
                'user_id',
                'type',
                'first_name',
                'last_name',
                'company_name',
                'address_line_1',
                'address_line_2',
                'city',
                'state',
                'postal_code',
                'country_code',
                'is_default',
                'is_billing',
                'is_shipping',
                'created_at',
                'updated_at',
            ]);

        /** @var Collection<int, Order> $orders */
        $orders = $user->orders()
            ->withoutGlobalScopes()
            ->with([
                'items' => static function ($itemQuery): void {
                    /** @var \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\OrderItem, \App\Models\Order> $itemQuery */
                    $itemQuery
                        ->select([
                            'id',
                            'order_id',
                            'product_id',
                            'name',
                            'sku',
                            'quantity',
                            'price',
                            'total',
                            'created_at',
                            'updated_at',
                        ]);
                },
            ])
            ->orderByDesc('created_at')
            ->get([
                'id',
                'user_id',
                'number',
                'status',
                'total',
                'currency',
                'created_at',
                'updated_at',
            ]);

        /** @var Collection<int, Review> $reviews */
        $reviews = $user->reviews()
            ->orderByDesc('created_at')
            ->get([
                'id',
                'product_id',
                'rating',
                'title',
                'body',
                'created_at',
                'updated_at',
            ]);

        /** @var Collection<int, UserWishlist> $wishlists */
        $wishlists = UserWishlist::query()
            ->withTrashed()
            ->with([
                'items' => static function ($itemQuery): void {
                    /** @var \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\WishlistItem, \App\Models\UserWishlist> $itemQuery */
                    $itemQuery
                        ->select([
                            'id',
                            'wishlist_id',
                            'product_id',
                            'variant_id',
                            'quantity',
                            'notes',
                            'created_at',
                            'updated_at',
                        ])
                        ->with(['product' => static function ($productQuery): void {
                            /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Product, \App\Models\WishlistItem> $productQuery */
                            $productQuery->select([
                                'id',
                                'name',
                                'slug',
                            ]);
                        }]);
                },
            ])
            ->where('user_id', $user->getKey())
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get([
                'id',
                'user_id',
                'name',
                'description',
                'is_public',
                'is_default',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);

        $wishlistProducts = $wishlists
            ->flatMap(static function (UserWishlist $wishlist) {
                /** @var Collection<int, WishlistItem> $items */
                $items = $wishlist->items;

                return $items->map(static function (WishlistItem $item) use ($wishlist): array {
                    $product = $item->product;

                    return [
                        'wishlist_id'   => $wishlist->getKey(),
                        'wishlist_name' => $wishlist->getAttribute('name'),
                        'product_id'    => $product?->getKey(),
                        'product_name'  => $product?->getAttribute('name'),
                        'variant_id'    => $item->getAttribute('variant_id'),
                        'quantity'      => $item->getAttribute('quantity'),
                        'notes'         => $item->getAttribute('notes'),
                        'added_at'      => $item->created_at?->toAtomString(),
                    ];
                });
            })
            ->values();

        $payload = [
            'meta' => [
                'generated_at' => now()->toAtomString(),
                'application'  => config('app.name'),
                'locale'       => app()->getLocale(),
                'user_id'      => $user->getKey(),
            ],
            'profile' => [
                'name'                     => $user->getAttribute('name'),
                'first_name'               => $user->getAttribute('first_name'),
                'last_name'                => $user->getAttribute('last_name'),
                'email'                    => $user->getAttribute('email'),
                'phone'                    => $user->getAttribute('phone') ?? $user->getAttribute('phone_number'),
                'locale'                   => $user->preferredLocale(),
                'created_at'               => $user->created_at?->toAtomString(),
                'updated_at'               => $user->updated_at?->toAtomString(),
                'marketing_preferences'    => (array) $user->getAttribute('marketing_preferences'),
                'privacy_settings'         => (array) $user->getAttribute('privacy_settings'),
                'notification_preferences' => (array) $user->getAttribute('notification_preferences'),
            ],
            'addresses' => $addresses->map(static fn (Address $address): array => [
                'id'             => $address->getKey(),
                'type'           => $address->getAttribute('type'),
                'first_name'     => $address->getAttribute('first_name'),
                'last_name'      => $address->getAttribute('last_name'),
                'company_name'   => $address->getAttribute('company_name'),
                'address_line_1' => $address->getAttribute('address_line_1'),
                'address_line_2' => $address->getAttribute('address_line_2'),
                'city'           => $address->getAttribute('city'),
                'state'          => $address->getAttribute('state'),
                'postal_code'    => $address->getAttribute('postal_code'),
                'country_code'   => $address->getAttribute('country_code'),
                'is_default'     => (bool) $address->getAttribute('is_default'),
                'is_billing'     => (bool) $address->getAttribute('is_billing'),
                'is_shipping'    => (bool) $address->getAttribute('is_shipping'),
                'created_at'     => $address->created_at?->toAtomString(),
                'updated_at'     => $address->updated_at?->toAtomString(),
            ])->values(),
            'orders' => $orders->map(static function (Order $order): array {
                /** @var Collection<int, OrderItem> $items */
                $items = $order->items;

                return [
                    'id'         => $order->getKey(),
                    'number'     => $order->getAttribute('number'),
                    'status'     => $order->getAttribute('status'),
                    'total'      => $order->getAttribute('total'),
                    'currency'   => $order->getAttribute('currency'),
                    'created_at' => $order->created_at?->toAtomString(),
                    'updated_at' => $order->updated_at?->toAtomString(),
                    'items'      => $items->map(static fn (OrderItem $item): array => [
                        'id'         => $item->getKey(),
                        'product_id' => $item->product_id,
                        'name'       => $item->name,
                        'sku'        => $item->sku,
                        'quantity'   => $item->quantity,
                        'price'      => $item->price,
                        'total'      => $item->total,
                    ])->values(),
                ];
            })->values(),
            'reviews' => $reviews->map(static fn (Review $review): array => [
                'id'         => $review->getKey(),
                'product_id' => $review->product_id,
                'rating'     => $review->rating,
                'title'      => $review->title,
                'body'       => $review->getAttribute('body'),
                'created_at' => $review->created_at?->toAtomString(),
                'updated_at' => $review->updated_at?->toAtomString(),
            ])->values(),
            'wishlist' => $wishlistProducts,
        ];

        $userKey = $user->getKey();
        if (! is_scalar($userKey)) {
            $userKey = $user->getAttribute($user->getKeyName());
        }

        $fileKey = is_scalar($userKey) ? (string) $userKey : '';
        $fileName = sprintf('personal-data-%s-%s.json', $fileKey, now()->format('YmdHis'));

        return response()->streamDownload(static function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $fileName, ['Content-Type' => 'application/json']);
    }

    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(static function () use ($user): void {
            /** @var User $account */
            $account = $user;

            $account->addresses()->delete();
            $account->reviews()->delete();

            $accountKey = $account->getKey();
            if (! is_scalar($accountKey)) {
                $accountKey = $account->getAttribute($account->getKeyName());
            }

            $emailKey = is_scalar($accountKey) ? (string) $accountKey : '';
            $anonymisedEmail = sprintf('deleted-user-%s@deleted.example', $emailKey);

            $account->forceFill([
                'name'                     => __('Deleted User'),
                'first_name'               => null,
                'last_name'                => null,
                'email'                    => $anonymisedEmail,
                'phone'                    => null,
                'phone_number'             => null,
                'privacy_settings'         => [],
                'marketing_preferences'    => [],
                'notification_preferences' => [],
                'preferences'              => [],
            ])->save();

            /** @phpstan-ignore-next-line */
            if (method_exists($account, 'tokens')) {
                /** @phpstan-ignore-next-line */
                $account->tokens()->delete();
            }

            $account->delete();
        });

        /** @var StatefulGuard $guard */
        $guard = Auth::guard();
        $guard->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with(
            'success',
            __('translations.profile_delete_success')
        );
    }
}
