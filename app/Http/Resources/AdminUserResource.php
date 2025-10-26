<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\Contracts\Entities\UserContract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class AdminUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contract = UserContract::fromModel($this->resource);
        $meta = is_array($contract['meta'] ?? null) ? $contract['meta'] : [];

        // Merge all of the computed properties that only exist on the admin
        // projection of a user into the existing contract payload.
        $meta = array_merge($meta, [
            'full_name'                 => $this->resource->full_name,
            'initials'                  => $this->resource->initials,
            'avatar_url'                => $this->resource->avatar_url,
            'is_email_verified'         => $this->resource->isEmailVerified(),
            'is_phone_verified'         => $this->resource->isPhoneVerified(),
            'has_two_factor'            => $this->resource->hasTwoFactor(),
            'is_on_trial'               => $this->resource->isOnTrial(),
            'has_active_subscription'   => $this->resource->hasActiveSubscription(),
            'subscription_status_color' => $this->resource->subscription_status_color,
            'status_color'              => $this->resource->status_color,
            'status_text'               => $this->resource->status_text,
            'age'                       => $this->resource->age,
            'gender_text'               => $this->resource->gender_text,
            'locale_text'               => $this->resource->locale_text,
            'roles_label'               => $this->resource->roles_label,
            'total_spent'               => $this->resource->total_spent,
            'average_order_value'       => $this->resource->average_order_value,
            'last_order_date'           => $this->resource->last_order_date,
            'orders_count'              => $this->resource->orders_count,
            'reviews_count'             => $this->resource->reviews_count,
            'average_rating'            => $this->resource->average_rating,
        ]);

        if ($this->resource->relationLoaded('addresses')) {
            $meta['addresses'] = $this->resource->addresses->map(static function ($address): array {
                // We cannot rely on Eloquent models exposing an `except` helper,
                // so remove the foreign key manually before serialising.
                return Arr::except($address->toArray(), ['user_id']);
            })->toArray();
        }

        if ($this->resource->relationLoaded('orders')) {
            $meta['orders'] = $this->resource->orders->map(static function ($order): array {
                // Order resources follow the same rule—strip the redundant
                // relation pointer before returning the array payload.
                return Arr::except($order->toArray(), ['user_id']);
            })->toArray();
        }

        if ($this->resource->relationLoaded('wishlist')) {
            $meta['wishlist'] = $this->resource->wishlist->map(static function ($product): array {
                // Wishlist entries are simple product arrays, so return them as-is.
                return $product->toArray();
            })->toArray();
        }

        if ($this->resource->relationLoaded('reviews')) {
            $meta['reviews'] = $this->resource->reviews->map(static function ($review): array {
                // Remove the user pointer to keep the payload clean for reviews too.
                return Arr::except($review->toArray(), ['user_id']);
            })->toArray();
        }

        if ($this->resource->relationLoaded('partners')) {
            $meta['partners'] = $this->resource->partners->map(static function ($partner): array {
                // Partners expose their complete data set in the admin panel.
                return $partner->toArray();
            })->toArray();
        }

        if ($this->resource->relationLoaded('referrals')) {
            $meta['referrals'] = $this->resource->referrals->map(static function ($referral): array {
                // Both referral foreign keys are redundant for API consumers, so strip them.
                return Arr::except($referral->toArray(), ['referrer_id', 'referred_id']);
            })->toArray();
        }

        $contract['meta'] = $meta;

        return $contract;
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'timestamp'  => now()->toISOString(),
                'version'    => '1.0',
                'admin_view' => true,
            ],
        ];
    }
}
