<?php

declare(strict_types=1);

namespace App\Http\Resources;

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
        // Use the except() method to exclude sensitive fields for admin display
        $safeAttributes = $this->resource->toAdminSafeArray();
        
        return array_merge($safeAttributes, [
            // Add computed fields that are safe for admin
            'full_name' => $this->resource->full_name,
            'initials' => $this->resource->initials,
            'avatar_url' => $this->resource->avatar_url,
            'is_email_verified' => $this->resource->isEmailVerified(),
            'is_phone_verified' => $this->resource->isPhoneVerified(),
            'has_two_factor' => $this->resource->hasTwoFactor(),
            'is_on_trial' => $this->resource->isOnTrial(),
            'has_active_subscription' => $this->resource->hasActiveSubscription(),
            'subscription_status_color' => $this->resource->subscription_status_color,
            'status_color' => $this->resource->status_color,
            'status_text' => $this->resource->status_text,
            'age' => $this->resource->age,
            'gender_text' => $this->resource->gender_text,
            'locale_text' => $this->resource->locale_text,
            'roles_label' => $this->resource->roles_label,
            
            // Add business metrics
            'total_spent' => $this->resource->total_spent,
            'average_order_value' => $this->resource->average_order_value,
            'last_order_date' => $this->resource->last_order_date,
            'orders_count' => $this->resource->orders_count,
            'reviews_count' => $this->resource->reviews_count,
            'average_rating' => $this->resource->average_rating,
            
            // Add relationships if loaded
            'addresses' => $this->whenLoaded('addresses', function () {
                return Arr::from($this->resource->addresses->map(function ($address) {
                    return $address->except(['user_id']);
                }));
            }),
            
            'orders' => $this->whenLoaded('orders', function () {
                return Arr::from($this->resource->orders->map(function ($order) {
                    return $order->except(['user_id']);
                }));
            }),
            
            'wishlist' => $this->whenLoaded('wishlist', function () {
                return Arr::from($this->resource->wishlist->map(function ($product) {
                    return $product;
                }));
            }),
            
            'reviews' => $this->whenLoaded('reviews', function () {
                return Arr::from($this->resource->reviews->map(function ($review) {
                    return $review->except(['user_id']);
                }));
            }),
            
            'partners' => $this->whenLoaded('partners', function () {
                return Arr::from($this->resource->partners->map(function ($partner) {
                    return $partner;
                }));
            }),
            
            'referrals' => $this->whenLoaded('referrals', function () {
                return Arr::from($this->resource->referrals->map(function ($referral) {
                    return $referral->except(['referrer_id', 'referred_id']);
                }));
            }),
        ]);
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
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
                'admin_view' => true,
            ],
        ];
    }
}
