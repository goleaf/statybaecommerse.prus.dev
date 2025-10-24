<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

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
        $userPayload = self::mapUser($user);

        return self::envelope($userPayload, $meta);
    }

    private static function mapUser(User $user): array
    {
        $relationsToLoad = [];

        // Guard optional relationships so migrations that omit commerce tables during tests do not trigger query exceptions.
        if (Schema::hasTable('addresses')) {
            $relationsToLoad[] = 'addresses';
        }

        if (Schema::hasTable('orders')) {
            $relationsToLoad[] = 'orders';
        }

        $hasWishlistTables = Schema::hasTable('user_wishlists')
            && Schema::hasColumn('user_wishlists', 'product_id')
            && Schema::hasTable('products')
            && Schema::hasTable('wishlist_items');

        if ($hasWishlistTables) {
            $relationsToLoad[] = 'wishlist';
        }

        if ($relationsToLoad !== []) {
            $user->loadMissing($relationsToLoad);
        }
        $safeAttributes = $user->toApiSafeArray();

        if ($hasWishlistTables) {
            $user->loadMissing(['wishlists.items.product']);
        }

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
            'addresses' => $user->relationLoaded('addresses')
                ? $user->addresses->map(static function ($address) {
                    return Arr::except($address->toArray(), ['user_id', 'created_at', 'updated_at']);
                })->all()
                : [],
            'orders' => $user->relationLoaded('orders')
                ? $user->orders->map(static function ($order) {
                    return Arr::except($order->toArray(), ['user_id']);
                })->all()
                : [],
            'wishlist' => $hasWishlistTables && $user->relationLoaded('wishlist')
                ? $user->wishlist->map(static function ($product) {
                    return Arr::except($product->toArray(), ['pivot']);
                })->all()
                : [],
            'links' => [
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
