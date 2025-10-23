<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Models\User;
use Illuminate\Support\Arr;

final class UserContract
{
    public const CONTRACT = 'user';

    public const VERSION = 'v1';

    public static function schemaPath(): string
    {
        return resource_path('contracts/v1/user.schema.json');
    }

    public static function examplePath(): string
    {
        return resource_path('contracts/v1/examples/user.json');
    }

    public static function forUser(User $user, array $meta = []): array
    {
        return self::envelope([
            'item' => self::mapUser($user),
        ], $meta);
    }

    private static function mapUser(User $user): array
    {
        // Preload relations that back the envelope so downstream JSON encoding
        // doesn't trigger N+1 queries for large dashboard payloads.
        $user->loadMissing(['addresses', 'orders', 'wishlists.items.product']);
        $safeAttributes = $user->toApiSafeArray();

        $wishlistItems = $user->wishlists
            ->flatMap(static function ($wishlist) {
                return $wishlist->items->map(static function ($item) use ($wishlist) {
                    return [
                        'wishlist_id'   => $wishlist->getKey(),
                        'wishlist_name' => (string) $wishlist->name,
                        'product'       => $item->product ? [
                            'id'   => $item->product->getKey(),
                            'name' => (string) $item->product->name,
                            'slug' => (string) $item->product->slug,
                        ] : null,
                        'variant_id' => $item->variant_id,
                        'quantity'   => $item->quantity,
                        'notes'      => $item->notes,
                        'added_at'   => $item->created_at?->toISOString(),
                    ];
                });
            })
            ->values()
            ->all();

        return [
            'id'         => $user->getKey(),
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'full_name'  => (string) $user->full_name,
            'initials'   => $user->initials,
            'avatar_url' => $user->avatar_url,
            'contact'    => [
                'email' => $safeAttributes['email'] ?? null,
                'phone' => $safeAttributes['phone_number'] ?? null,
            ],
            'status' => [
                'is_email_verified'       => $user->isEmailVerified(),
                'is_phone_verified'       => $user->isPhoneVerified(),
                'has_two_factor'          => $user->hasTwoFactor(),
                'is_on_trial'             => $user->isOnTrial(),
                'has_active_subscription' => $user->hasActiveSubscription(),
            ],
            'metrics' => [
                'orders_count'        => (int) $user->orders_count,
                'reviews_count'       => (int) $user->reviews_count,
                'total_spent'         => (float) $user->total_spent,
                'average_order_value' => (float) $user->average_order_value,
                'last_order_date'     => $user->last_order_date ? (string) $user->last_order_date : null,
            ],
            'preferences' => [
                'preferred_locale' => $user->preferred_locale,
                'timezone'         => $user->timezone,
                'locale_text'      => $user->locale_text,
                'roles_label'      => $user->roles_label,
            ],
            'addresses' => $user->addresses->map(static function ($address) {
                return Arr::except($address->toArray(), ['user_id', 'created_at', 'updated_at']);
            })->all(),
            'orders' => $user->orders->map(static function ($order) {
                return Arr::except($order->toArray(), ['user_id']);
            })->all(),
            'wishlist' => $wishlistItems,
            'links'    => [
                'self' => route('account'),
            ],
        ];
    }

    private static function envelope(array $data, array $meta = []): array
    {
        $meta = array_merge([
            'generated_at' => now()->toISOString(),
        ], Arr::whereNotNull($meta));

        return [
            'contract' => self::CONTRACT,
            'version'  => self::VERSION,
            'data'     => $data,
            'meta'     => $meta,
        ];
    }
}
